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
	 * Get a file from the repository
	 *
	 * @param string $path
	 * @return File
	 */
	public function get_file ($path)
	{
		$path = preg_replace('/^\//', '', $path);
		$full_path = $this->path . '/' . $path;

		if (!file_exists($full_path))
		{
			return null;
		}

		return new File($this, $path);
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
