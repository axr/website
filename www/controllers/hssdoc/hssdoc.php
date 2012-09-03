<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(ROOT . '/models/hssdoc_object.php');
require_once(ROOT . '/models/hssdoc_property.php');
require_once(ROOT . '/models/hssdoc_value.php');

class HssdocController extends WWWController
{
	/**
	 * Display /doc
	 */
	public function run ()
	{
		$this->view->sidebar = $this->render_sidebar();

		echo $this->renderView(ROOT . '/views/hssdoc.html');
	}

	/**
	 * Display HSS object pages
	 */
	public function run_object ($object_name)
	{
		$object = HssdocObject::find_by_name($object_name, array(
			'readonly' => true
		));

		if ($object === null)
		{
			throw new HTTPException(null, 404);
		}

		$properties = HssdocProperty::find('all', array(
			'conditions' => array('object = ?', $object_name),
			'order' => 'name asc',
			'readonly' => true
		));

		if (!is_array($properties))
		{
			throw new HTTPException(null, 404);
		}

		$this->view->_title = $object->name;
		$this->breadcrumb[] = array(
			'name' => 'HSS documentation',
			'link' => '/doc'
		);
		$this->breadcrumb[] = array(
			'name' => $object->name
		);

		foreach ($properties as &$property)
		{
			$property->_values_table = $this->render_values_table($property->id);
		}

		$this->view->object = $object;
		$this->view->properties = $properties;
		$this->view->sidebar = $this->render_sidebar();

		echo $this->renderView(ROOT . '/views/hssdoc_obj.html');
	}

	/**
	 * Create the sidebar for HSS documentation pages
	 *
	 * @return string HTML
	 */
	private function render_sidebar ()
	{
		$objects = HssdocObject::find('all', array(
			'order' => 'name asc'
		));

		// For some reason Mustache can't access properties directly from
		// the HssdocObject model
		foreach ($objects as &$object)
		{
			$item = $object->attributes();
			$item['properties'] = $object->properties;

			$object = $item;
		}

		$view = new StdClass();
		$view->objects = $objects;
		$view->has_objects = count($objects) > 0;

		$mustache = new Mustache();
		$template = file_get_contents(ROOT . '/views/hssdoc_sidebar.html');

		return $mustache->render($template, $view);
	}

	/**
	 * Render HSS documentation values table
	 *
	 * @param int $property_id
	 * @return string
	 */
	private function render_values_table ($property_id)
	{
		$data = HssdocValue::find('all', array(
			'conditions' => array('property_id = ?', $property_id),
			'order' => 'version asc',
			'readonly' => true
		));

		if (!is_array($data))
		{
			return null;
		}

		$first_of_ver = array();

		foreach ($data as $i => &$value)
		{
			// So we can do whatever we want with it
			$value = (object) $value->attributes();

			if (!isset($first_of_ver[$value->version]))
			{
				$value->_count = 0;
				$first_of_ver[$value->version] = &$value;
			}

			$first_of_ver[$value->version]->_count++;
		}

		if (count($data) === 0)
		{
			return null;
		}

		$view = new StdClass();
		$view->values = $data;

		$mustache = new Mustache();
		$template = file_get_contents(ROOT . '/views/hssdoc_values_table.html');

		return $mustache->render($template, $view);
	}
}

