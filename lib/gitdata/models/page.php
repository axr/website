<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class Page extends \GitData\Model
{
	/**
	 * Type of the page
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Title for the page
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Date of creation
	 *
	 * @var string
	 */
	public $date;

	/**
	 * Author's name
	 *
	 * @var string
	 */
	public $author_name;

	/**
	 * Content of the page
	 *
	 * @var string
	 */
	public $content;

	/**
	 * Summary of the page. This property is filled only for pages of type
	 * `blog-post`.
	 *
	 * @var string
	 */
	public $summary;

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

		// Set the permalink
		$this->permalink = preg_replace('/^pages/', '', dirname($info_file->path));

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

		// Get the summary
		if ($this->type === 'blog-post')
		{
			if (isset($info->summary_file))
			{
				// Read the summary file
				$summary_file = \GitData\GitData::$repo->get_file(
					dirname($info_file->path) . '/' . $info->summary_file);

				if ($summary_file !== null)
				{
					$this->summary = self::parse_content($summary_file);
				}
			}

			if (empty($this->summary))
			{
				$explode = explode('<!--more-->', $this->content);
				$this->summary = $explode[0];
			}
		}
	}

	/**
	 * Find a page by path name from the URL.
	 *
	 * @param string $path
	 * @return \GitData\Models\Page
	 */
	public static function find_by_path ($path)
	{
		$path = preg_replace('/^\//', '', $path);
		$info_file = \GitData\GitData::$repo->get_file('/pages/' . $path . '/info.json');

		if ($info_file === null)
		{
			return null;
		}

		try
		{
			return new Page($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
