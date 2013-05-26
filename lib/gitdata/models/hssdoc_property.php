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
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		// Set some defaults
		$this->attrs_data = (object) array(
			'readonly' => false,
			'permanent' => false,
			'many_values' => false,
			'values' => array()
		);

		parent::__construct($info_file);

		$implemented_count = 0;

		foreach ($this->attrs_data->values as &$value)
		{
			$value = (object) array_merge(array(
				'value' => null,
				'is_default' => false,
				'since_version' => null
			), (array) $value);

			if ($value->since_version !== null)
			{
				$implemented_count++;
			}

			unset($value);
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
		preg_match('/^hssdoc\/(@\w+)\//', $info_file->path, $match);
		$this->object_name = $match[1];

		// Set the permalink
		$this->permalink = '/' . $this->object_name . '#' . $this->attrs_data->name;

		// Read the description
		if (isset($this->attrs_data->description_file))
		{
			$path = dirname($info_file->path) . '/' . $this->attrs_data->description_file;
			$file = \GitData\GitData::$repo->get_file($path);

			if ($file !== null)
			{
				$this->description = (string) new \GitData\Content($file);
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
