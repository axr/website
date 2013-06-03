<?php

namespace Hssdoc;

class SidebarView extends \Core\View
{
	/**
	 * __construct
	 */
	public function __construct ()
	{
		parent::__construct(ROOT . '/views/sidebar.html');

		$this->data['tree'] = $this->render_tree($this->build_tree());
	}

	/**
	 * Recursively render the object tree
	 *
	 * @param \GitData\Models\HssdocObject[] $objects
	 * @return string
	 */
	protected function render_tree (array $objects)
	{
		foreach ($objects as $object)
		{
			if (count($object->_children) > 0)
			{
				$object->_tree = $this->render_tree($object->_children);
			}

			$object->properties = $object->get_properties();
		}

		$view = new \Core\View(ROOT . '/views/sidebar_tree.html');

		$view->objects = $objects;
		$view->IMPL_NONE = \GitData\Models\HssdocProperty::IMPL_NONE;
		$view->IMPL_SEMI = \GitData\Models\HssdocProperty::IMPL_SEMI;
		$view->IMPL_FULL = \GitData\Models\HssdocProperty::IMPL_FULL;

		return $view->get_rendered();
	}

	/**
	 * Build the objects tree
	 *
	 * @return \GitData\Models\HssdocObject[]
	 */
	protected function build_tree ()
	{
		$root = \GitData\GitData::$root . '/hssdoc';
		$objects_dir = scandir($root);

		$objects = array();

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
				$object->_children = array();
				$objects[$object_name] = $object;
			}
		}

		// Make the associations
		foreach ($objects as $object_name => $object)
		{
			if (is_string($object->owner) &&
				isset($objects[$object->owner]))
			{
				$objects[$object->owner]->_children[] = $object;
			}
		}

		// Clean up
		foreach ($objects as $object_name => $object)
		{
			if ($object->owner !== null)
			{
				unset($objects[$object_name]);
			}
		}

		return $objects;
	}
}
