<?php

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocObject extends \ActiveRecord\Model
{
	static $table_name = 'www_hssdoc_objects';

	static $has_many = array(
		array('properties',
			'class_name' => 'HssdocProperty',
			'foreign_key' => 'object')
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
}

