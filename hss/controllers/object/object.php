<?php

namespace Hssdoc;

class ObjectController extends Controller
{
	public function run ($object_name)
	{
		$object = \GitData\Models\HssdocObject::find_by_name($object_name);

		$properties = $object->get_properties();

		foreach ($properties as &$property)
		{
			$property->_values_table = $this->render_values_table($property);
		}

		$this->view->_title = $object->name;
		$this->breadcrumb[] = array(
			'name' => $object->name
		);

		$this->view->object = $object;
		$this->view->properties = $properties;

		echo $this->renderView(ROOT . '/views/object.html');
	}

	/**
	 * Render HSS documentation values table
	 *
	 * @param \GitData\Models\HssdocProperty
	 * @return string
	 */
	private function render_values_table (\GitData\Models\HssdocProperty $property)
	{
		if (count($property->values) === 0)
		{
			return null;
		}

		$values = $property->values;
		$first_of_ver = array();

		foreach ($values as $i => &$value)
		{
			if (!isset($first_of_ver[$value->since_version]))
			{
				$value->_count = 0;
				$first_of_ver[$value->since_version] = &$value;
			}

			if (preg_match('/^(?<object>@[a-zA-Z0-9]+)(<(?<property>[a-zA-Z0-9]+)>)?$/', $value->value, $match))
			{
				$value->_ref_url = \Router::get_instance()->url
					->copy()
					->path('/' . $match['object']);

				if (isset($match['property']))
				{
					$value->_ref_url->fragment($match['property']);
				}
			}

			$first_of_ver[$value->since_version]->_count++;
		}

		$view = new \StdClass();
		$view->values = $values;
		$view->property = $property;

		$mustache = new \Mustache\Renderer();
		$template = file_get_contents(ROOT . '/views/values_table.html');

		return $mustache->render($template, $view);
	}
}
