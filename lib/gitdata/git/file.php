<?php

namespace GitData\Git;

class File
{
	/**
	 * The repository which this file belongs to.
	 *
	 * @var Repository
	 */
	protected $repository;

	/**
	 * Path to the file, relative to the repository's root.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * __construct
	 *
	 * @param Repository $repository
	 * @param string $path
	 */
	public function __construct (Repository $repository, $path)
	{
		$this->repository = $repository;
		$this->path = $path;
	}

	/**
	 * Get a commit that has edited this file
	 *
	 * @param int $skip
	 * @return Commit
	 */
	public function get_commit ($skip = 0)
	{
		$skip = (int) $skip;

		$sha = $this->repository->run_command('log', array('-n 1',
			'--pretty="%H"',
			'--skip=' . $skip,
			' -- "' . $this->path . '"'));

		if ($sha === null)
		{
			return null;
		}

		return new Commit($this->repository, $sha);
	}
}
