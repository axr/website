<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocProperty
{
	/**
	 * Name of the parent object
	 *
	 * @var string
	 */
	public $object_name;

	/**
	 * Name of the property
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Is this property readonly or not?
	 *
	 * @var bool
	 */
	public $readonly = false;

	/**
	 * Whether this property supports many values
	 *
	 * @var bool
	 */
	public $many_values = false;

	/**
	 * Values list for this property
	 *
	 * @var StdClass[]
	 */
	public $values = array();

	/**
	 * Permalink for the property
	 *
	 * @var string
	 */
	public $permalink;

	/**
	 * Description of the property
	 *
	 * @var string
	 */
	public $description;

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

		foreach ($this->values as &$value)
		{
			$value = (object) array_merge(array(
				'value' => null,
				'is_default' => false,
				'since_version' => null
			), (array) $value);

			unset($value);
		}

		// Extract the parent object name
		preg_match('/^hssdoc\/(@\w+)\//', $info_file->path, $match);
		$this->object_name = $match[1];

		// Set the permalink
		$this->permalink = '/' . $this->object_name . '#' . $info->name;

		// Read the description
		if (isset($info->description_file))
		{
			$file = \GitData\GitData::$repo->get_file(
				dirname($info_file->path) . '/' . $info->description_file);

			if ($file !== null)
			{
				$this->description = self::parse_content($file);
			}
		}
	}

	/**
	 * Parse stuff like the page content and summary.
	 *
	 * @todo Move to \GitData\Model
	 * @param \GitData\Git\File $file
	 */
	protected static function parse_content (\GitData\Git\File $file)
	{
		$data = $file->get_data();

		if (self::get_content_type($file->path) === 'md')
		{
			$data = Markdown($data);
		}

		if (in_array(self::get_content_type($file->path), array('md', 'html')))
		{
			$data = \GitData\Asset::replace_urls_in_html(dirname($file->path), $data);
		}

		return $data;
	}

	/**
	 * Returns the type of the content file (or of the path specified)
	 * Possible values: md|html|text
	 *
	 * @todo Move to \GitData\Model
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
	 * Find all properties by an object name
	 *
	 * @todo cache the result
	 * @param string $object_name
	 * @return \GitData\Models\HssdocProperty[]
	 */
	public static function find_all_by_object ($object_name)
	{
		$object_dir = \GitData\GitData::$root . '/hssdoc/' . $object_name;

		if (!is_dir($object_dir))
		{
			return array();
		}

		$filenames = scandir($object_dir);
		$properties = array();

		foreach ($filenames as $filename)
		{
			if (!preg_match('/^property-(.+)\.json$/', $filename))
			{
				continue;
			}

			$info_file = \GitData\GitData::$repo->get_file(
				'hssdoc/' . $object_name . '/' . $filename);

			if ($info_file === null)
			{
				continue;
			}

			try
			{
				$properties[] = new HssdocProperty($info_file);
			}
			catch (\GitData\Exceptions\EntityInvalid $e)
			{
			}
		}

		// TODO sort the properties

		return $properties;
	}
}
