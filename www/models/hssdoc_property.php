<?php

namespace WWW;

require_once(ROOT . '/models/hssdoc_value.php');
require_once(SHARED . '/lib/core/model.php');

class HssdocProperty extends \Core\Model
{
	static $table_name = 'www_hssdoc_properties';

	static $before_destroy = array('before_destroy');

	static $attr_accessible = array('name', 'description', 'readonly');

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
	 * Get display url for the property
	 *
	 * @return \URL
	 */
	public function get_display_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/' . $this->object)
			->fragment($this->name);
	}

	/**
	 * Get edit url for the property
	 *
	 * @return \URL
	 */
	public function get_edit_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/edit_property/' . $this->id);
	}

	/**
	 * Get delete url for the property
	 *
	 * @return \URL
	 */
	public function get_rm_url ()
	{
		return \URL::create()
			->from_string(\Config::get('/shared/hssdoc_url'))
			->path('/' . $this->object . '/' . $this->name . '/rm');
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
