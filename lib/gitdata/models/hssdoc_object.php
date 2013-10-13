<?php

namespace GitData\Models;

class HssdocObject extends \GitData\Model
{
	protected $attrs = array('name', 'owner', 'description_file', 'shorthand_stack');
	protected $public = array('description', 'permalink');

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
		parent::__construct($info_file);

		// Set the permalink
		$this->permalink = preg_replace('/^hssdoc/', '', dirname($info_file->path));

		// Read the description
		if (isset($this->description_file))
		{
			$path = dirname($info_file->path) . '/' . $this->description_file;
			$file = \GitData\GitData::$repo->get_file($path);

			if ($file !== null)
			{
				$this->description = (string) new \GitData\Content($file);
			}
		}

		$this->_cache_write_state();
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
	 * Get the object's owner
	 *
	 * @return \GitData\Models\HssdocObject
	 */
	public function get_owner ()
	{
		return self::find_by_name($this->owner);
	}

	/**
	 * Get a property by its name
	 *
	 * @param string $name
	 * @return \GitData\Models\HssdocProperty
	 */
	public function get_property_by_name ($name)
	{
		$info_file = \GitData\GitData::$repo->get_file(
			'hssdoc/' . $this->name . '/property-' . $name . '.json');

		if ($info_file === null)
		{
			return null;
		}

		try
		{
			return new HssdocProperty($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
		}

		return null;
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
			return HssdocObject::new_instance($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
