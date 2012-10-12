<?php

class Config
{
	/**
	 * All configuration options are stored here
	 *
	 * @var mixed[]
	 */
	public static $options = array();

	/**
	 * Set an option
	 *
	 * @param string $path
	 * @param mixed $data
	 */
	public static function set ($path, $data)
	{
		self::$options[$path] = $data;
	}

	/**
	 * Get an option
	 *
	 * @param string $path
	 * @return mixed
	 */
	public static function get ($path)
	{
		return isset(self::$options[$path]) ? self::$options[$path] : null;
	}
}

