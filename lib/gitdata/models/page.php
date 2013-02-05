<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class Page extends \GitData\Model
{
	/**
	 * @var \GitData\File
	 */
	protected $info_file;

	/**
	 * @var \GitData\File
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
	 * @param \GitData\File $info_file
	 */
	public function __construct (\GitData\File $info_file)
	{
		$this->info_file = $info_file;

		$this->info = json_decode($info_file->data);

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

		$this->content_file = \GitData\File::try_read_file($content_path);

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
		return preg_replace('/^\/pages/', '', dirname($this->info_file->path));
	}

	/**
	 * Returns the type of the content file.
	 * Possible values: md|html|text
	 *
	 * @return string
	 */
	public function get_content_type ()
	{
		// Extract the file extension
		$explode = explode('.', $this->content_file->path);
		$extension = end($explode);

		return in_array($extension, array('md', 'html')) ? $extension : 'text';
	}

	/**
	 * Returns the parsed content of the page
	 *
	 * @return string
	 */
	public function get_content ()
	{
		if (!is_string($this->_parsed_content))
		{
			if ($this->get_content_type() === 'md')
			{
				$this->_parsed_content = Markdown($this->content_file->data);
			}
			else
			{
				$this->_parsed_content = $this->content_file->data;
			}
		}

		return $this->_parsed_content;
	}

	/**
	 * Get a short summery for this page. This method is mostly used for blog
	 * posts.
	 *
	 * @return string
	 */
	public function get_summary ()
	{
		$content = $this->get_content();
		$explode = explode('<!--more-->', $content);

		return $explode[0];
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
		$info_file = \GitData\File::try_read_file('/pages/' . $path . '/info.json');

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
