<?php

namespace MustacheFilters;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class JSON extends \Mustache\Filter
{
	/**
	 * Set filter name
	 */
	public $name = 'json';

	/**
	 * Filter
	 */
	public function filter ($data)
	{
		return json_encode($data);
	}
}
