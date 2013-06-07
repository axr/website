<?php

namespace GitData;

abstract class Model
{
	/**
	 * The model's info file
	 *
	 * @var \GitData\Git\File
	 */
	protected $info_file;

	/**
	 * Attributes that will be copied over from the model's JSON data file
	 *
	 * @var string[]
	 */
	protected $attrs;

	/**
	 * Properties of this class that should be publicly readable
	 *
	 * @var string[]
	 */
	protected $public;

	/**
	 * Data of the attributes from the model's JSON data file
	 */
	protected $attrs_data;

	/**
	 * Constructor
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		$this->info_file = $info_file;

		if (!is_object($this->attrs_data))
		{
			$this->attrs_data = (object) array();
		}

		$info = json_decode($info_file->get_data());

		if (!is_object($info))
		{
			throw new \GitData\Exceptions\EntityInvalid(null);
		}

		foreach ($info as $key => $value)
		{
			if (in_array($key, $this->attrs))
			{
				$this->attrs_data->{$key} = $value;
			}
		}
	}

	/**
	 * __get
	 *
	 * @param string $key
	 */
	public function __get ($key)
	{
		if (isset($this->{$key}) && in_array($key, $this->public))
		{
			return $this->{$key};
		}

		if (isset($this->attrs_data->{$key}))
		{
			return $this->attrs_data->{$key};
		}
	}

	/**
	 * __isset
	 *
	 * @param string $key
	 */
	public function __isset ($key)
	{
		if (isset($this->{$key}) && in_array($key, $this->public))
		{
			return true;
		}

		if (isset($this->attrs_data->{$key}))
		{
			return true;
		}

		return false;
	}

	public static function new_instance (\GitData\Git\File $info_file)
	{
		$cached = \Cache::get(self::_cache_get_key($info_file));

		if ($cached === null)
		{
			$reflection = new \ReflectionClass(get_called_class());
			return $reflection->newInstance($info_file);
		}
		else
		{
			return $cached;
		}
	}

	protected static function _cache_get_key (\GitData\Git\File $info_file)
	{
		return '/gitdata/model/' .
			preg_replace('/^.+?([^\\\\]+)$/', '$1', get_called_class()) .
			'/file:' . $info_file->get_unique_id();
	}

	/**
	 * Write the current model state to the cache
	 */
	protected function _cache_write_state ()
	{
		if ($this->info_file === null)
		{
			return;
		}

		$key = self::_cache_get_key($this->info_file);

		if ($key !== null)
		{
			\Cache::set($key, $this, array(
				'expires' => 0
			));
		}
	}
}
