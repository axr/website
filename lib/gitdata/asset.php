<?php

namespace GitData;

require_once(SHARED . '/lib/core/url.php');

class Asset
{
	/**
	 * Returns if the provided file is a valid asset and can be accessed
	 * publicly
	 *
	 * @param \GitData\Git\File $file
	 */
	public static function is_asset ($file)
	{
		$explode = explode('/', $file->path);

		if (!in_array($explode[0], array('pages', 'wiki', 'hssdoc')))
		{
			return false;
		}

		return true;
	}

	/**
	 * Replace all the URLs of static assets in HTML code.
	 *
	 * @param string $path
	 * @param string $html
	 * @return string
	 */
	public static function replace_urls_in_html ($path, $html)
	{
		if (!\GitData\GitData::$repo->file_exists($path . '/info.json'))
		{
			return $html;
		}

		$html = preg_replace_callback('/<img ([^>]+)?src="(?<path>[^"]+)"/', function ($match) use ($path)
		{
			$file = \GitData\GitData::$repo->get_file($path . '/' . $match['path']);

			if ($file === null ||
				!Asset::is_asset($file))
			{
				return $match[0];
			}

			$url = \URL::create(\Config::get()->url->www)
				->path('/gitdata/asset')
				->query('path', $file->path)
				->to_string();

			return str_replace($match["path"], $url, $match[0]);
		}, $html);

		return $html;
	}
}
