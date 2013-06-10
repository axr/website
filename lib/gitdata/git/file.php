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
	public $path;

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
	 * Read the file
	 *
	 * @return mixed
	 */
	public function get_data ()
	{
		return file_get_contents(\GitData\GitData::$root . '/' . $this->path);
	}

	/**
	 * Get the size of the file
	 *
	 * @return int
	 */
	public function get_size ()
	{
		return strlen($this->get_data());
	}

	/**
	 * Get a commit that has edited this file.
	 *
	 * Warning: This process seems quite slow, so be careful.
	 * @todo Cache this
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

		return new Commit($this->repository,
			preg_replace('/[^a-f0-9]/', '', $sha));
	}

	/**
	 * Quickly get a unique id for this file. The ID returned by this method
	 * can change even if the file doesn't.
	 *
	 * @return string
	 */
	public function get_unique_id ()
	{
		return hash('sha1', $this->path . \GitData\GitData::$version);
	}
}
