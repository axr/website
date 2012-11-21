<?php

namespace MustacheFilters;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class Markdown extends \Mustache\Filter
{
	/**
	 * Set filter name
	 */
	public $name = 'markdown';

	/**
	 * Filter
	 */
	public function filter ($data)
	{
		return Markdown($data);
	}
}
