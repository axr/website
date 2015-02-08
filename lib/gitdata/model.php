<?php

namespace GitData;

abstract class Model
{
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
	public function __construct ($info)
	{
		if (!is_object($this->attrs_data))
		{
			$this->attrs_data = (object) array();
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
}
