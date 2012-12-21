<?php

namespace WWW;

require_once(SHARED . '/lib/core/model.php');
require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocValue extends \Core\Model
{
	static $table_name = 'www_hssdoc_values';

	static $after_save = array('clear_others_default');

	static $attr_accessible = array('value', 'version', 'default');

	static $validates_presence_of = array(
		array('value')
	);

	/**
	 * Remove the "default" flag from every value but this one
	 */
	public function clear_others_default ()
	{
		if ($this->default)
		{
			// Don't do this at home
			\ActiveRecord\Connection::instance('default')->query('
				UPDATE `www_hssdoc_values` AS `value`
				SET `value`.`default` = 0
				WHERE `value`.`property_id` = ' . $this->property_id . ' AND
					`value`.`id` <> ' . $this->id);
		}

		// Do it at a friend's home
	}
}
