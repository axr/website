<?php

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocObject extends \ActiveRecord\Model
{
	static $table_name = 'www_hssdoc_objects';

	static $after_construct = array('virtual_fields');

	static $validates_presence_of = array(
		array('name')
	);

	static $has_many = array(
		array('properties',
			'class_name' => 'HssdocProperty',
			'primary_key' => 'name',
			'foreign_key' => 'object')
	);

	/**
	 * Permalink to the property
	 *
	 * @var string
	 */
	public $permalink;

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
	 * Create virtual fields, like permalink
	 */
	public function virtual_fields ()
	{
		$this->permalink = '/doc/' . $this->name;
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

