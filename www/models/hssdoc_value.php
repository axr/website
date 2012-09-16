<?php

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocValue extends \ActiveRecord\Model
{
	static $table_name = 'www_hssdoc_values';

	static $attr_accessible = array('value', 'version', 'default');

	static $validates_presence_of = array(
		array('version'),
		array('value')
	);
}

