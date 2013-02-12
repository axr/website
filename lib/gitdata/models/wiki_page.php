<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class WikiPage extends \GitData\Model
{
	/**
	 * Title for the page
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Content of the page
	 *
	 * @var string
	 */
	public $content;

	/**
	 * @var string
	 */
	public $mtime_str = '0000-00-00 00:00';

	/**
	 * The last person that edited this file
	 *
	 * @var string
	 */
	public $last_author;

	/**
	 * URL to the GitHub history page
	 *
	 * @var string
	 */
	public $github_history_url;

	/**
	 * __construct
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		$info = json_decode($info_file->get_data());

		if (!is_object($info))
		{
			throw new \GitData\Exceptions\EntityInvalid(null);
		}

		foreach ($info as $key => $value)
		{
			if (property_exists(__CLASS__, $key))
			{
				$this->$key = $value;
			}
		}

		// Read the content
		{
			if (isset($info->file))
			{
				$content_path = dirname($info_file->path) . '/' . $info->file;
			}
			else
			{
				$content_path = dirname($info_file->path) . '/content.md';
			}

			$content_file = \GitData\GitData::$repo->get_file($content_path);

			if ($content_file === null)
			{
				throw new \GitData\Exceptions\EntityInvalid(null);
			}

			$this->content = self::parse_content($content_file);
		}

		$this->permalink = preg_replace('/^wiki/', '', dirname($info_file->path));
		$this->github_history_url = 'https://github.com/axr/website-data/commits/master/' .
			$content_file->path;

		// Get last modified date and last author
		{
			$commit = $content_file->get_commit();

			if ($commit !== null)
			{
				$this->mtime_str = date('Y-m-d H:i', $commit->date);
				$this->last_author = $commit->author;
			}
		}
	}

	/**
	 * Find a page by path name from the URL.
	 *
	 * @param string $path
	 * @return \GitData\Models\WikiPage
	 */
	public static function find_by_path ($path)
	{
		$path = preg_replace('/^\//', '', $path);
		$file = \GitData\GitData::$repo->get_file('wiki/' . $path . '/info.json');

		if ($file === null)
		{
			return null;
		}

		try
		{
			return new WikiPage($file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
