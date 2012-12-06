<?php

namespace MustacheFilters;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class Filesize extends \Mustache\Filter
{
	/**
	 * Set filter name
	 */
	public $name = 'filesize';

	/**
	 * Filter
	 */
	public function filter ($data)
	{
		$size = (int) $data;

		$table = array(
			$size < pow(1024, 4) => array(pow(1024, 3), 'GiB'),
			$size < pow(1024, 3) => array(pow(1024, 2), 'MiB'),
			$size < pow(1024, 2) => array(pow(1024, 1), 'KiB'),
			$size < 1024 => array(1, 'B'),
		);

		return number_format($size / $table[true][0], 2) . ' ' . $table[true][1];
	}
}
