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
	 */
	public function __construct ($compound)
	{
		parent::__construct($compound->info);

		// Set the permalink
		$this->permalink = preg_replace('/^hssdoc/', '', $compound->info->_basedir);

		if (isset($info->_filename))
		{
			$this->permalink .= preg_replace('/\..*$/', '', $compound->info->_filename);
		}

		$this->description = (string) $compound->content;

		if (!$this->description && isset($compound->info->description_file))
		{
			$this->description = (string) new \GitData\Content($compound->info,
				$compound->info->description_file);
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
	 * Get the object's owner
	 *
	 * @return \GitData\Models\HssdocObject
	 */
	public function get_owner ()
	{
		return self::find_by_name($this->owner);
	}

	/**
	 * Find an object by it's name
	 *
	 * @param string $name
	 * @return \GitData\Models\HssdocObject
	 */
	public static function find_by_name ($name)
	{
		$compound = \GitData\Compound::load(array(
			'hssdoc/' . $name . '/info.json',
			'hssdoc/' . $name . '/object.md'
		));

		if ($compound)
		{
			return new HssdocObject($compound);
		}
	}

	/**
	 * Find all objects.
	 */
	public static function find_all ()
	{
		$object = git_object_lookup_bypath(\GitData\GitData::$tree, 'hssdoc', GIT_OBJ_TREE);

		if (!$object)
		{
			return array();
		}

		$tree = git_tree_lookup(\GitData\GitData::$repo, git_object_id($object));

		if (!$tree)
		{
			return array();
		}

		$objects = array();

		git_tree_walk($tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$objects)
		{
			$name = git_tree_entry_name($entry);

			if ($name[0] !== '@')
			{
				return 1;
			}

			$object = HssdocObject::find_by_name($name);

			if ($object)
			{
				$objects[$name] = $object;
			}
		}, $objects);

		return $objects;
	}
}
