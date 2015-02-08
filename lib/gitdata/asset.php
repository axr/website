<?php

namespace GitData;

require_once(SHARED . '/lib/core/url.php');

class Asset
{
	/**
	 * Replace all the URLs of static assets in HTML code.
	 *
	 * @param string $basedir
	 * @param string $html
	 * @return string
	 */
	public static function replace_urls_in_html ($basedir, $html)
	{
		$html = preg_replace_callback('/<img ([^>]+)?src="(?<path>[^"]+)"/', function ($match) use ($basedir)
		{
			$url = \URL::create(\Config::get()->url->www)
				->path('/gitdata/asset')
				->query('path', $basedir . '/' . $match['path'])
				->to_string();

			return str_replace($match['path'], $url, $match[0]);
		}, $html);

		return $html;
	}
}
