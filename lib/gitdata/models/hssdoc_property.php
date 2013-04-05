<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocProperty extends \GitData\Model
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
	 * Is this property permanent or not?
	 *
	 * @var bool
	 */
	public $permanent = false;

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

		usort($properties, function ($a, $b)
		{
			return strcmp($a->name, $b->name);
		});

		return $properties;
	}
}
