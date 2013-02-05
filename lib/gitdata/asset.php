<?php

namespace GitData;

require_once(SHARED . '/lib/core/url.php');

class Asset
{
	/**
	 * Path to the asset
	 *
	 * @var string
	 */
	public $path;

	/**
	 * Publicly accessible absolute URL to the asset
	 *
	 * @var \URL
	 */
	public $url;

	/**
	 * __construct
	 *
	 * @param string $path
	 */
	public function __construct ($path)
	{
		$this->path = $path;
		$this->url = \Config::get('/shared/www_url')
			->copy()
			->path('/gitdata/asset')
			->query('path', $path);
	}

	/**
	 * Get the asset's data
	 *
	 * @return mixed
	 */
	public function get_data ()
	{
		return file_get_contents(\GitData\GitData::$root . '/' . $this->path);
	}

	/**
	 * Return the last modified time for the asset
	 *
	 * @return int
	 */
	public function get_mtime ()
	{
		return filemtime(\GitData\GitData::$root . '/' . $this->path);
	}

	/**
	 * Return an MD5 checksum for the file
	 *
	 * @return string
	 */
	public function get_md5_checksum ()
	{
		return md5_file(\GitData\GitData::$root . '/' . $this->path);
	}

	/**
	 * Return the asset's mime type
	 *
	 * @return string
	 */
	public function get_mime_type ()
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file($finfo, \GitData\GitData::$root . '/' . $this->path);
	}

	/**
	 * Get an asset by it's path name
	 */
	public static function get_by_path ($path)
	{
		if (!File::file_exists($path))
		{
			return null;
		}

		$explode = explode('/', $path);

		if (!in_array($explode[1], array('pages', 'wiki', 'hssdoc')))
		{
			return null;
		}

		return new Asset($path);
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
		if (!File::file_exists($path . '/info.json'))
		{
			return $html;
		}

		$html = preg_replace_callback('/<img ([^>]+)?src="(?<path>[^"]+)"/', function ($match) use ($path)
		{
			$asset = self::get_by_path($path . '/' . $match["path"]);

			if ($asset === null)
			{
				return $match[0];
			}

			return str_replace($match["path"], $asset->url->to_string(), $match[0]);
		}, $html);

		return $html;
	}
}
