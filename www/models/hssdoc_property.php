<?php

require_once(ROOT . '/models/hssdoc_value.php');
require_once(SHARED . '/lib/core/model.php');

class HssdocProperty extends \Core\Model
{
	static $table_name = 'www_hssdoc_properties';

	static $before_destroy = array('before_destroy');

	static $has_many = array(
		array('values',
			'class_name' => 'HssdocValue',
			'foreign_key' => 'property_id')
	);

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
		if ($attribute_name === 'permalink')
		{
			return true;
		}

		return parent::__isset($attribute_name);
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
		if (User::current()->can('/hssdoc/edit'))
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
		if (User::current()->can('/hssdoc/rm'))
		{
			return true;
		}

		return false;
	}
}
