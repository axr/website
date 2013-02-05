<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class Page extends \GitData\Model
{
	/**
	 * @var \GitData\Git\File
	 */
	protected $info_file;

	/**
	 * @var \GitData\Git\File
	 */
	protected $content_file;

	/**
	 * Parsed info file of the page
	 *
	 * @var \StdClass
	 */
	protected $info;

	protected $_parsed_content;

	/**
	 * __construct
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		$this->info_file = $info_file;

		$this->info = json_decode($info_file->get_data());
		if (!is_object($this->info))
		{
			throw new \GitData\Exceptions\EntityInvalid(null);
		}

		// Get path to the content file
		if (isset($this->info->file))
		{
			$content_path = dirname($this->info_file->path) . '/' . $this->info->file;
		}
		else
		{
			$content_path = dirname($this->info_file->path) . '/content.md';
		}

		$this->content_file = \GitData\GitData::$repo->get_file($content_path);

		if ($this->content_file === null)
		{
			throw new \GitData\Exceptions\EntityInvalid(null);
		}
	}

	/**
	 * Returns the permalink for this page
	 *
	 * @return string
	 */
	public function get_permalink ()
	{
		return preg_replace('/^pages/', '', dirname($this->info_file->path));
	}

	/**
	 * Returns the parsed content of the page
	 *
	 * @return string
	 */
	public function get_content ()
	{
		return $this->parse_content($this->content_file);
	}

	/**
	 * Get a short summery for this page. This method is mostly used for blog
	 * posts.
	 *
	 * @return string
	 */
	public function get_summary ()
	{
		if (isset($this->info->summary_file))
		{
			// Read the summary file
			$summary_file = \GitData\GitData::$repo->get_file(
				dirname($this->info_file->path) . '/' . $this->info->summary_file);

			if ($summary_file !== null)
			{
				return $this->parse_content($summary_file);
			}
		}

		$content = $this->get_content();
		$explode = explode('<!--more-->', $content);

		return $explode[0];
	}

	/**
	 * Parse stuff like the page content and summary.
	 *
	 * @param \GitData\Git\File $file
	 */
	protected function parse_content (\GitData\Git\File $file)
	{
		$data = $file->get_data();

		if (self::get_content_type($file->path) === 'md')
		{
			$data = Markdown($data);
		}

		if (in_array(self::get_content_type($file->path), array('md', 'html')))
		{
			$data = \GitData\Asset::replace_urls_in_html(
				dirname($this->info_file->path), $data);
		}

		return $data;
	}

	/**
	 * Returns the type of the content file (or of the path specified)
	 * Possible values: md|html|text
	 *
	 * @param string $path
	 * @return string
	 */
	protected static function get_content_type ($path)
	{
		// Extract the file extension
		$explode = explode('.', $path);
		$extension = end($explode);

		return in_array($extension, array('md', 'html')) ? $extension : 'text';
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
