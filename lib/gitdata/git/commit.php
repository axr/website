<?php

namespace GitData\Git;

class Commit
{
	/**
	 * The repository which this commit belongs to.
	 *
	 * @var Repository
	 */
	protected $repository;

	/**
	 * SHA of the commit
	 *
	 * @var string
	 */
	protected $sha;

	/**
	 * Author of the commit
	 *
	 * @var string
	 */
	public $author;

	/**
	 * Date of the commit
	 *
	 * @var int
	 */
	public $date;

	/**
	 * __construct
	 *
	 * @param Repository $repository
	 * @param string $path
	 */
	public function __construct (Repository $repository, $sha)
	{
		$this->repository = $repository;
		$this->sha = $sha;

		$raw = $this->repository->run_command('cat-file', array('commit', $sha));

		if ($raw !== null)
		{
			preg_match('/^author\s+(?<author>.+?\s+<[^>]+>)\s+(?<date>[0-9]+)\s+(?<tz>[+-][0-9]+)$/m', $raw, $match);

			if (is_array($match))
			{
				$this->author = $match['author'];
				$this->date = $match['date'];
			}
		}
	}
}
