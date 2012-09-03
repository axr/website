<?php

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocProperty extends \ActiveRecord\Model
{
	static $table_name = 'www_hssdoc_properties';

	static $after_construct = array('virtual_fields');

	static $validates_presence_of = array(
		array('name'),
		array('description')
	);

	/**
	 * Permalink to the property
	 *
	 * @var string
	 */
	public $permalink;

	/**
	 * HTML code for the values table
	 *
	 * @var string
	 */
	public $_values_table;

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
		$this->permalink = '/doc/' . $this->object . '#' . $this->name;
	}

	/**
	 * Decide if the user can edit this property
	 *
	 * @return bool
	 */
	public function can_edit ()
	{
		if (Session::perms()->has('*') ||
			Session::perms()->has('/hssdoc/*') ||
			Session::perms()->has('/hssdoc/edit'))
		{
			return true;
		}

		return false;
	}
}

