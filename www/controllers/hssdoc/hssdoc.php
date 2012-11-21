<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(ROOT . '/models/hssdoc_object.php');
require_once(ROOT . '/models/hssdoc_property.php');
require_once(ROOT . '/models/hssdoc_value.php');
require_once(SHARED . '/lib/core/exceptions/http_ajax.php');
require_once(SHARED . '/lib/mustache_filters/markdown.php');

class HssdocController extends WWWController
{
	public function initialize ()
	{
		\Mustache\Filter::register(new \MustacheFilters\Markdown);

		// The documentation is not being requested from hss.axr.vg domain
		if(Router::get_instance()->url->host !==
			(new URL(Config::get('/shared/hssdoc_url')))->host)
		{
			throw new HTTPException(null, 404);
		}

		// Add the common breadcrumb item
		$this->breadcrumb[] = array(
			'name' => 'HSS documentation',
			'link' => URL::create(Config::get('/shared/hssdoc_url'))
		);
	}

	/**
	 * Display /doc
	 */
	public function run ()
	{
		$this->view->_title = 'HSS documentation';
		$this->view->sidebar = $this->render_sidebar();

		if (User::current()->can('/hssdoc/edit'))
		{
			$this->tabs[] = array(
				'name' => 'New object',
				'link' => URL::create()
					->from_string(Config::get('/shared/hssdoc_url'))
					->path('/add_object')
			);
		}

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
			'name' => $object->name
		);

		$this->tabs[] = array(
			'name' => 'View',
			'link' => $object->display_url,
			'current' => true
		);

		if ($object->can_edit())
		{
			$this->tabs[] = array(
				'name' => 'Edit',
				'link' => $object->edit_url
			);
		}

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
	 * Display add/edit page for objects
	 *
	 * @param string $mode (add|edit)
	 * @param mixed $arg
	 */
	public function run_edit_object ($mode = 'add', $arg = null)
	{
		if ($mode === 'add')
		{
			$object = new HssdocObject();
		}
		else
		{
			try
			{
				$object = HssdocObject::find_by_name($arg);
			}
			catch (\ActiveRecord\RecordNotFound $e)
			{
				throw new HTTPException(null, 404);
			}
		}

		if (!$object->can_edit())
		{
			throw new HTTPException(null, 403);
		}

		if (isset($_POST['_via_post']))
		{
			$object->set_attributes(array(
				'name' => array_key_or($_POST, 'name', null),
				'description' => array_key_or($_POST, 'description', null)
			));

			if ($object->save())
			{
				// We want to always redirect after edit in case the object
				// name gets changed

				$this->redirect($object->edit_url);

				return;
			}

			if ($object->is_invalid())
			{
				$this->view->errors = $object->errors->full_messages();
				$this->view->has_errors = true;
			}

		}

		if ($mode === 'add')
		{
			$this->view->_title = 'New object';
			$this->breadcrumb[] = array(
				'name' => 'New object'
			);
		}
		else
		{
			$this->view->_title = 'Edit object';
			$this->breadcrumb[] = array(
				'name' => 'Edit object'
			);

			$this->tabs[] = array(
				'name' => 'View',
				'link' => $object->display_url
			);
			$this->tabs[] = array(
				'name' => 'Edit',
				'link' => $object->edit_url,
				'current' => true
			);
		}

		$this->view->object = $object;
		$this->view->properties = $object->properties;
		$this->view->edit_mode = $mode === 'edit';
		$this->view->add_property_url = URL::create()
			->from_string(Config::get('/shared/hssdoc_url'))
			->path('/add_property/' . $object->name);

		echo $this->renderView(ROOT . '/views/hssdoc_edit_object.html');
	}

	/**
	 * Display edit/add page for properties
	 *
	 * @param string $mode (add|edit)
	 * @param mixed $arg
	 */
	public function run_edit_property ($mode = 'add', $arg = null)
	{
		if ($mode === 'add')
		{
			$property = new HssdocProperty();

			$count = HssdocObject::count(array(
				'conditions' => array('name = ?', $arg)
			));

			if ((int) $count === 0)
			{
				throw new HTTPException(null, 404);
			}

			$property->object = $arg;
		}
		else
		{
			try
			{
				$property = HssdocProperty::find($arg);
			}
			catch (\ActiveRecord\RecordNotFound $e)
			{
				throw new HTTPException(null, 404);
			}
		}

		if (!$property->can_edit())
		{
			throw new HTTPException(null, 403);
		}

		if (isset($_POST['_via_post']))
		{
			$property->set_attributes(array(
				'name' => array_key_or($_POST, 'name', null),
				'description' => array_key_or($_POST, 'description', null),
				'readonly' => array_key_or($_POST, 'readonly', 0)
			));

			if ($property->save() && $mode === 'add')
			{
				$this->redirect($property->display_url);
				return;
			}

			if ($property->is_invalid())
			{
				$this->view->errors = $property->errors->full_messages();
				$this->view->has_errors = true;
			}

		}

		$this->breadcrumb[] = array(
			'name' => $property->object,
			'link' => $property->display_url
		);

		if ($mode === 'add')
		{
			$this->view->_title = 'New property';
			$this->breadcrumb[] = array(
				'name' => 'New property'
			);
		}
		else
		{
			$this->view->_title = 'Edit property';
			$this->breadcrumb[] = array(
				'name' => 'Edit property'
			);

			$this->tabs[] = array(
				'name' => 'View',
				'link' => $property->display_url
			);
			$this->tabs[] = array(
				'name' => 'Edit',
				'link' => $property->edit_url,
				'current' => true
			);
		}

		$this->view->property = $property->attributes();
		$this->view->edit_mode = $mode === 'edit';

		echo $this->renderView(ROOT . '/views/hssdoc_edit_property.html');
	}

	/**
	 * Remove an object
	 *
	 * @todo display the error message in a nicer way
	 */
	public function run_rm_object ($name)
	{
		try
		{
			$object = HssdocObject::find_by_name($name);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new HTTPException(null, 404);
		}

		if (!$object->can_rm())
		{
			throw new HTTPException(null, 404);
		}

		if (!$object->is_empty())
		{
			throw new HTTPException('Object is not empty', 403);
		}

		$this->view->_title = 'Delete object';
		$this->breadcrumb[] = array(
			'name' => 'Delete object'
		);

		$this->view->object = $object;

		if (isset($_POST['_via_post']))
		{
			$object->delete();
			$this->redirect(URL::create(Config::get('/shared/hssdoc_url')));
		}

		echo $this->renderView(ROOT . '/views/hssdoc_rm_object.html');
	}

	/**
	 * Remove property
	 *
	 * @param string $object_name
	 * @param string $property_name
	 */
	public function run_rm_property ($object_name, $property_name)
	{
		try
		{
			$property = HssdocProperty::find(array(
				'conditions' => array(
					'object = ? AND name = ?', $object_name, $property_name
				)
			));
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new HTTPException(null, 404);
		}

		if (!$property->can_rm())
		{
			throw new HTTPException(null, 404);
		}

		$this->view->_title = 'Delete property';
		$this->breadcrumb[] = array(
			'name' => 'Delete property'
		);

		$this->view->property = $property;

		if (isset($_POST['_via_post']))
		{
			$property->delete();

			$this->redirect(URL::create()
				->from_string(Config::get('/shared/hssdoc_url'))
				->path('/' . $object_name));
		}

		echo $this->renderView(ROOT . '/views/hssdoc_rm_property.html');
	}

	/**
	 * GET /doc/property_values.json
	 */
	public function run_property_values_GET ()
	{
		if (!isset($_GET['property']))
		{
			throw new HTTPAjaxException(null, 400);
		}

		try
		{
			$property = HssdocProperty::find($_GET['property']);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new HTTPAjaxException(null, 404);
		}

		$data = array();

		foreach ($property->values as $value)
		{
			$data[] = $value->attributes();
		}

		echo json_encode(array(
			'status' => 0,
			'payload' => $data
		));
	}

	/**
	 * POST /doc/property_values.json
	 */
	public function run_property_values_POST ()
	{
		if (!User::current()->can('/hssdoc/edit'))
		{
			throw new HTTPAjaxException(null, 403);
		}

		if (isset($_POST['id']))
		{
			try
			{
				$item = HssdocValue::find($_POST['id']);
			}
			catch (\ActiveRecord\RecordNotFound $e)
			{
			}
		}

		if (!isset($item) || !is_object($item))
		{
			$item = new HssdocValue();

			// Check if property exists
			if (!isset($_POST['property_id']) ||
				HssdocProperty::count($_POST['property_id']) === 0)
			{
				throw new HTTPAjaxException(null, 400);
			}

			$item->property_id = (int) $_POST['property_id'];
		}

		$item->set_attributes($_POST);
		$item->save();

		if ($item->is_valid())
		{
			echo json_encode(array(
				'status' => 0,
				'payload' => $item->attributes()
			));
		}
		else
		{
			echo json_encode(array(
				'status' => 1,
				'error' => 'ValidationFailed',
				'validation_errors' => $item->errors->full_messages()
			));
		}
	}

	/**
	 * DELETE /doc/property_values.json
	 */
	public function run_property_values_DELETE ()
	{
		parse_str(file_get_contents("php://input"), $_DELETE);

		if (!User::current()->can('/hssdoc/rm'))
		{
			throw new HTTPAjaxException(null, 403);
		}

		if (!isset($_DELETE['id']))
		{
			throw new HTTPAjaxException(null, 400);
		}

		try
		{
			$item = HssdocValue::find($_DELETE['id']);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new HTTPAjaxException(null, 404);
		}

		$item->delete();

		echo json_encode(array(
			'status' => 0
		));
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

		$view = new StdClass();
		$view->objects = $objects;
		$view->can_edit = User::current()->can('/hssdoc/edit');

		$mustache = new \Mustache\Renderer();
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

		$mustache = new \Mustache\Renderer();
		$template = file_get_contents(ROOT . '/views/hssdoc_values_table.html');

		return $mustache->render($template, $view);
	}
}
