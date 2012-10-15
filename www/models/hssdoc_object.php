<?php

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
			'order' => 'readonly asc, name asc')
	);

	/**
	 * __isset
	 *
	 * @param string $attribute_name
	 * @return bool
	 */
	public function __isset ($attribute_name)
	{
		if ($attribute_name === 'description__parsed')
		{
			return true;
		}

		return parent::__isset($attribute_name);
	}

	/**
	 * Getter for attribute description__parsed
	 *
	 * @return string
	 */
	public function get_description__parsed ()
	{
		return Markdown($this->description);
	}

	/**
	 * Getter for attribute permalink
	 *
	 * @return string
	 */
	public function get_permalink ()
	{
		return '/doc/' . $this->name;
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
