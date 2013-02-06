<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocObject extends \GitData\Model
{
	/**
	 * @var \GitData\Git\File
	 */
	protected $info_file;

	/**
	 * Parsed info file of the page
	 *
	 * @var \StdClass
	 */
	protected $info;

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
	}

	/**
	 * Get all the properties that belong to this object
	 *
	 * @return \GitData\Models\HssdocProperty[]
	 */
	public function get_properties ()
	{
		return \GitData\Models\HssdocProperty::find_all_by_object($this->info->name);
	}

	/**
	 * Returns the permalink for this page
	 *
	 * @return string
	 */
	public function get_permalink ()
	{
		return preg_replace('/^hssdoc/', '', dirname($this->info_file->path));
	}

	/**
	 * Returns the parsed description of the object
	 *
	 * @return string
	 */
	public function get_description ()
	{
		if (isset($this->info->description_file))
		{
			// Read the summary file
			$file = \GitData\GitData::$repo->get_file(
				dirname($this->info_file->path) . '/' . $this->info->description_file);

			if ($file !== null)
			{
				return $this->parse_content($file);
			}
		}
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
	 * Find an object by it's name
	 *
	 * @param string $name
	 * @return \GitData\Models\HssdocObject
	 */
	public static function find_by_name ($name)
	{
		$info_file = \GitData\GitData::$repo->get_file('/hssdoc/' . $name . '/info.json');

		if ($info_file === null)
		{
			return null;
		}

		try
		{
			return new HssdocObject($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
