<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class HssdocObject extends \GitData\Model
{
	/**
	 * Name of the object
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Permalink for the object
	 *
	 * @var string
	 */
	public $permalink;

	/**
	 * Description of the object
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

		// Set the permalink
		$this->permalink = preg_replace('/^hssdoc/', '', dirname($info_file->path));

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
	 * Get all the properties that belong to this object
	 *
	 * @return \GitData\Models\HssdocProperty[]
	 */
	public function get_properties ()
	{
		return \GitData\Models\HssdocProperty::find_all_by_object($this->name);
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
