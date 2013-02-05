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
		$path = preg_replace('/[.]+/', '.', $path);
		$real_path = GitData::$root . '/' . $path;

		if (!file_exists($real_path))
		{
			return null;
		}

		return new File($path, file_get_contents($real_path));
	}
}
