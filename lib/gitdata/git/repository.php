<?php

namespace GitData\Git;

class Repository
{
	/**
	 * Path to the git repository
	 *
	 * @param string
	 */
	protected $path;

	/**
	 * __construct
	 *
	 * @param string $path
	 */
	public function __construct ($path)
	{
		$this->path = $path;
	}

	/**
	 * Check, if a file exists
	 *
	 * @param string $path
	 * @return bool
	 */
	public function file_exists ($path)
	{
		$path = preg_replace('/^\//', '', $path);
		return file_exists($this->path . '/' . $path);
	}

	/**
	 * Get a file from the repository
	 *
	 * @param string $path
	 * @return File
	 */
	public function get_file ($path)
	{
		$path = preg_replace('/^\//', '', $path);

		if ($this->file_exists($path))
		{
			return new File($this, $path);
		}

		return null;
	}

	/**
	 * Run a command on this repository
	 *
	 * @param string $cmd
	 * @param string[] $args
	 * @return mixed
	 */
	public function run_command ($cmd, $args)
	{
		return shell_exec("git --git-dir=\"{$this->path}/.git\" {$cmd} " . implode(' ', $args));
	}
}
