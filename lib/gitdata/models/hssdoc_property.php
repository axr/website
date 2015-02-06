<?php

namespace GitData\Models;

class HssdocProperty extends \GitData\Model
{
	const IMPL_NONE = 0;
	const IMPL_SEMI = 1;
	const IMPL_FULL = 2;

	protected $attrs = array('name', 'readonly', 'permanent', 'many_values',
		'values', 'text_scope', 'description_file');
	protected $public = array('object_name', 'permalink', 'description',
		'implemented');

	/**
	 * Name of the parent object
	 *
	 * @var string
	 */
	public $object_name;

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
	 * Implementation status of this property
	 *
	 * @var int
	 */
	public $implemented;

	/**
	 * __construct
	 */
	public function __construct ($compound)
	{
		// Set some defaults
		$this->attrs_data = (object) array(
			'readonly' => false,
			'permanent' => false,
			'many_values' => false,
			'values' => array()
		);

		parent::__construct($compound->info);

		$implemented_count = 0;

		foreach ($this->attrs_data->values as &$value)
		{
			$value = (object) array_merge(array(
				'value' => null,
				'is_default' => false,
				'since_version' => null
			), (array) $value);

			if ($this->attrs_data->readonly === true)
			{
				// It doesn't make sense for a readonly property to have a
				// default value.
				$value->is_default = false;
			}

			if ($value->since_version !== null)
			{
				$implemented_count++;
			}

			unset($value);
		}

		if ($this->attrs_data->readonly === true &&
			$this->attrs_data->many_values === true)
		{
			// This doesn't make sense, so it's not allowed
			$this->attrs_data->many_values = false;
		}

		if ($implemented_count === count($this->values))
		{
			$this->implemented = self::IMPL_FULL;
		}
		else if ($implemented_count === 0)
		{
			$this->implemented = self::IMPL_NONE;
		}
		else
		{
			$this->implemented = self::IMPL_SEMI;
		}

		// Extract the parent object name
		preg_match('/^hssdoc\/(@\w+)/', $compound->info->_basedir, $match);
		$this->object_name = $match[1];

		$this->permalink = '/' . $this->object_name . '#' . $this->attrs_data->name;
		$this->description = (string) $compound->content;
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
		$git_object = git_object_lookup_bypath(\GitData\GitData::$tree, 'hssdoc/' . $object_name, GIT_OBJ_TREE);

		if (!$git_object)
		{
			return array();
		}

		$tree = git_tree_lookup(\GitData\GitData::$repo, git_object_id($git_object));

		if (!$tree)
		{
			return array();
		}

		$properties = array();

		git_tree_walk($tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$properties)
			use ($object_name)
		{
			$name = git_tree_entry_name($entry);
			$path = 'hssdoc/' . $object_name . '/' . $name;

			if (git_tree_entry_filemode($entry) !== GIT_FILEMODE_BLOB ||
				!preg_match('/^property-(.+)\.(json|md)$/', $name))
			{
				return 1;
			}

			$compound = \GitData\Compound::load($path);

			if ($compound && isset($compound->info->name))
			{
				$properties[] = new HssdocProperty($compound);
			}
		}, $properties);

		usort($properties, function ($a, $b)
		{
			return strcmp($a->name, $b->name);
		});

		return $properties;
	}
}
