<?php

namespace WWW;

require_once(SHARED . '/lib/core/model.php');
require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocObject extends \Core\Model
{
	static $table_name = 'www_hssdoc_objects';

	static $validates_presence_of = array(
		array('name')
	);

	static $has_many = array(
		array('properties',
			'class_name' => 'HssdocProperty',
			'primary_key' => 'name',
			'foreign_key' => 'object',
			'order' => 'name asc')
	);

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
}
