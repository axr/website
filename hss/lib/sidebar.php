<?php

namespace Hssdoc;

class Sidebar
{
	/**
	 * Render the sidebar
	 *
	 * @return string
	 */
	public static function render ()
	{
		$objects = self::get_objects_tree();

		$view = new \StdClass();
		$view->tree = self::render_sidebar_tree($objects);

		$mustache = new \Mustache\Renderer();
		$template = file_get_contents(ROOT . '/views/sidebar.html');

		return $mustache->render($template, $view);
	}

	protected static function render_sidebar_tree (array $objects)
	{
		foreach ($objects as $object)
		{
			if (is_array($object->child_objects) &&
				count($object->child_objects) > 0)
			{
				$object->tree_html = self::render_sidebar_tree($object->child_objects);
			}

			$object->properties = $object->get_properties();
		}

		$view = new \StdClass();
		$view->objects = $objects;

		$mustache = new \Mustache\Renderer();
		$template = file_get_contents(ROOT . '/views/sidebar_tree.html');

		return $mustache->render($template, $view);
	}

	/**
	 * Build a guide that can be used to generate the objects tree
	 *
	 * @return StdClass[]
	 */
	protected static function build_objects_tree_guide ()
	{
		$root = \GitData\GitData::$root . '/hssdoc';

		$objects_dir = scandir($root);
		$guide = array();

		// Find all the objects
		foreach ($objects_dir as $object_name)
		{
			if ($object_name[0] !== '@')
			{
				continue;
			}

			$object = \GitData\Models\HssdocObject::find_by_name($object_name);

			if ($object !== null)
			{
				$guide[$object_name] = (object) array(
					'children' => array(),
					'owner' => $object->owner
				);
			}
		}

		// Make the associations
		foreach ($guide as $object_name => $data)
		{
			if (is_string($data->owner))
			{
				if (isset($guide[$data->owner]))
				{
					$guide[$data->owner]->children[$object_name] = $data;
				}
			}
		}

		// Clean up
		foreach ($guide as $object_name => $data)
		{
			if ($data->owner !== null)
			{
				unset($guide[$object_name]);
			}
		}

		return $guide;
	}

	/**
	 * Build the object tree by using a guide
	 *
	 * @param StdClass[] $guide
	 * @return \GitData\Models\HssdocObject[]
	 */
	protected static function build_object_tree_by_guide ($guide)
	{
		$out = array();

		foreach ($guide as $object_name => $data)
		{
			$parent_object = \GitData\Models\HssdocObject::find_by_name($object_name);
			$parent_object->child_objects = array();

			foreach ($data->children as $child_name => $child)
			{
				$object = \GitData\Models\HssdocObject::find_by_name($child_name);
				$object->child_objects = array();

				if (count($child->children) > 0)
				{
					$object->child_objects = self::build_object_tree_by_guide($child->children);
				}

				$parent_object->child_objects[] = $object;
			}

			$out[] = $parent_object;
		}

		return $out;
	}

	/**
	 * Get the objects tree
	 *
	 * @return \GitData\Models\HssdocObject[]
	 */
	protected static function get_objects_tree ()
	{
		$guide = \Cache::get('/hss/objects_tree_guide');

		if ($guide === null)
		{
			$guide = self::build_objects_tree_guide();
			\Cache::set('/hss/objects_tree_guide', $guide, array(
				'data_version' => 'current'
			));
		}

		return self::build_object_tree_by_guide($guide);
	}
}
