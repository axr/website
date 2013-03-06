<?php

class Config
{
	/**
	 * All configuration options are stored here
	 *
	 * @var \StdClass
	 */
	public static $options;

	/**
	 * Load configuration values from a JSON file
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function load_from_file ($path)
	{
		if (!file_exists($path))
		{
			return false;
		}

		$data = file_get_contents($path);
		$data = json_decode($data);

		if (!is_object($data))
		{
			return false;
		}

		object_merge_recursive(self::$options, $data);

		return true;
	}

	/**
	 * Set an option
	 *
	 * @param string $path
	 * @param mixed $data
	 */
	public static function set ($path, $data)
	{
		self::$options->$path = $data;
	}

	/**
	 * Get an option
	 *
	 * @param string $path
	 * @return mixed
	 */
	public static function get ($path = null)
	{
		if ($path === null)
		{
			return self::$options;
		}

		return isset(self::$options->$path) ? self::$options->$path : null;
	}
}

Config::$options = new \StdClass();
