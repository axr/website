<?php

namespace WWW;

require_once(SHARED . '/lib/core/model.php');
require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocObject extends \Core\Model
{
	static $table_name = 'www_hssdoc_objects';
	static $before_save = array('before_save');

	static $attr_accessible = array('description', 'owner_id');

	static $validates_presence_of = array(
		array('name')
	);

	static $belongs_to = array(
		array('owner',
			'class_name' => '\\WWW\\HssdocObject',
			'foreign_key' => 'owner_id')
	);

	static $has_many = array(
		array('properties',
			'class_name' => '\\WWW\\HssdocProperty',
			'primary_key' => 'name',
			'foreign_key' => 'object',
			'order' => 'name asc')
	);

	/**
	 * For the select menus
	 */
	public $is_selected = false;

	/**
	 * Override `set_attribute` method
	 *
	 * @param array $attributes
	 */
	public function set_attributes (array $attributes)
	{
		if (isset($attributes['name']) && $this->is_new_record())
		{
			// The name can be set when creating the record, but not when editing
			$this->name = $attributes['name'];
		}

		return parent::set_attributes($attributes);
	}

	/**
	 * `before_save` callback
	 *
	 * @return bool
	 */
	public function before_save ()
	{
		if ($this->owner_id == 0)
		{
			$this->owner_id = null;
		}

		return true;
	}

	/**
	 * Get all normal (non-readonly) properties
	 *
	 * @return HssdocProperty[]
	 */
	public function get_properties_normal ()
	{
		$properties = array();

		foreach ($this->properties as $property)
		{
			if ((bool) $property->readonly === false)
			{
				$properties[] = $property;
			}
		}

		return $properties;
	}

	/**
	 * Get all read-only properties
	 *
	 * @return HssdocProperty[]
	 */
	public function get_properties_ro ()
	{
		$properties = array();

		foreach ($this->properties as $property)
		{
			if ((bool) $property->readonly === true)
			{
				$properties[] = $property;
			}
		}

		return $properties;
	}

	/**
	 * Get display URL for the object
	 *
	 * @return \URL
	 */
	public function get_display_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/' . $this->name);
	}

	/**
	 * Get edit URL for the object
	 *
	 * @return \URL
	 */
	public function get_edit_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/' . $this->name . '/edit');
	}

	/**
	 * Get delete URL for the object
	 *
	 * @return \URL
	 */
	public function get_rm_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/' . $this->name . '/rm');
	}

	/**
	 * Get URL for creating a new property
	 *
	 * @return \URL
	 */
	public function get_add_property_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/add_property/' . $this->name);
	}

	/**
	 * Check if this object contains any properties
	 *
	 * @return true
	 */
	public function is_empty ()
	{
		return count($this->properties) === 0;
	}

	/**
	 * Decide if the user can edit this property
	 *
	 * @return bool
	 */
	public function can_edit ()
	{
		return User::current()->can('/hssdoc/edit');
	}

	/**
	 * Decide if the user can remove this property
	 *
	 * @return bool
	 */
	public function can_rm ()
	{
		return User::current()->can('/hssdoc/rm');
	}

	/**
	 * Get a list of objects for a `<select>` drop-down
	 *
	 * @return HssdocObject[]
	 */
	public function get_all_for_select ()
	{
		$objects = HssdocObject::find('all');
		$selection = array();

		foreach ($objects as $object)
		{
			if ($object->id === $this->id)
			{
				continue;
			}

			if ($this->owner &&
				$object->id === $this->owner->id)
			{
				$object->is_selected = true;
			}

			$selection[] = $object;
		}

		return $selection;
	}
}
