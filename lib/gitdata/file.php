<?php

namespace GitData;

class File
{
	public $path;
	public $data;

	public function __construct ($path, $data)
	{
		$this->path = $path;
		$this->data = $data;
	}

	/**
	 * A setter, to make this function read-only
	 */
	public function __set ($k, $v)
	{
	}

	public static function try_read_file ($path)
	{
		$real_path = GitData::$root . '/' . self::make_path_safe($path);

		if (!file_exists($real_path))
		{
			return null;
		}

		return new File($path, file_get_contents($real_path));
	}

	/**
	 * Check, if a file exists
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function file_exists ($path)
	{
		$real_path = GitData::$root . '/' . self::make_path_safe($path);
		return file_exists($real_path) && is_file($real_path);
	}

	/**
	 * Check, if a directory exists
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function directory_exists ($path)
	{
		$real_path = GitData::$root . '/' . self::make_path_safe($path);
		return file_exists($real_path) && is_dir($real_path);
	}

	/**
	 * Make a user-provided path safe to use
	 *
	 * @param string $path
	 * @return string
	 */
	public static function make_path_safe ($path)
	{
		return preg_replace('/[.]+/', '.', $path);
	}
}
