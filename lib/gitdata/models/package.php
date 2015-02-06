<?php

namespace GitData\Models;

class Package extends \GitData\Model
{
	/**
	 * The git tree for this package.
	 */
	private $tree;

	/**
	 * Name of the package
	 *
	 * @var string
	 */
	public $name;

	/**
	 * __construct
	 *
	 * @param string $name
	 */
	public function __construct ($name, $tree)
	{
		$this->name = $name;
		$this->tree = $tree;
	}

	/**
	 * Get a release by version number. The version `latest` will automatically
	 * match the latest release.
	 *
	 * @param string $version
	 */
	public function get_release ($version)
	{
		if ($version === 'latest')
		{
			$version = $this->get_latest_version_number();
		}

		if ($version === null)
		{
			return null;
		}

		return PackageRelease::find_by_package_and_version($this->name, $version);
	}

	/**
	 * Return all the releases for this package.
	 *
	 * @return \GitData\Models\PackageRelease[]
	 */
	public function get_all_releases ()
	{
		$cache_key = '/gitdata/package/' . $this->name . '/all_releases';
		$releases = \Cache::get($cache_key);

		if ($releases !== null)
		{
			return $releases;
		}

		$releases = array();
		$self = $this;

		git_tree_walk($this->tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$releases)
			use (&$self)
		{
			$name = git_tree_entry_name($entry);

			if (!preg_match('/^release-(.+)\.json$/', $name, $match))
			{
				return 1;
			}

			$release = PackageRelease::find_by_package_and_version($self->name, $match[1]);

			if ($release)
			{
				$releases[] = $release;
			}
		}, $releases);

		usort($releases, function ($a, $b)
		{
			return strcmp($b->version, $a->version);
		});

		\Cache::set($cache_key, $releases, array(
			'data_version' => 'current'
		));

		return $releases;
	}

	/**
	 * Find the latest version number of this package
	 *
	 * @return string
	 */
	public function get_latest_version_number ()
	{
		$latest = null;

		git_tree_walk($this->tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$latest)
		{
			$name = git_tree_entry_name($entry);

			if (!preg_match('/^release-(.+)\.json$/', $name, $match))
			{
				return 1;
			}

			if (!$latest || strcmp($latest, $match[1]) < 0)
			{
				$latest = $match[1];
			}
		}, $latest);

		return $latest;
	}

	/**
	 * Find a package by it's name
	 *
	 * @param string $path
	 * @return \GitData\Models\Page
	 */
	public static function find_by_name ($name)
	{
		$object = git_object_lookup_bypath(\GitData\GitData::$tree, 'packages/' . $name, GIT_OBJ_TREE);

		if (!$object)
		{
			return null;
		}

		$tree = git_tree_lookup(\GitData\GitData::$repo, git_object_id($object));

		if (!$tree)
		{
			return null;
		}

		return new Package($name, $tree);
	}
}
