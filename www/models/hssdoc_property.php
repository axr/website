<?php

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocProperty extends \ActiveRecord\Model
{
	static $table_name = 'www_hssdoc_properties';

	static $before_destroy = array('before_destroy');

	static $validates_presence_of = array(
		array('name')
	);

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
		$virtual_fields = array('description__parsed', 'permalink');

		if (in_array($attribute_name, $virtual_fields))
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
		return '/doc/' . $this->object . '#' . $this->name;
	}

	/**
	 * Call before destroying the property
	 *
	 * @todo remove values
	 */
	public function before_destroy ()
	{
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

	/**
	 * Decide if the user can remove this property
	 *
	 * @return bool
	 */
	public function can_rm ()
	{
		if (Session::perms()->has('*') ||
			Session::perms()->has('/hssdoc/*') ||
			Session::perms()->has('/hssdoc/rm'))
		{
			return true;
		}

		return false;
	}
}

