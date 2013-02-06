<?php

namespace GitData\Models;

class Package extends \GitData\Model
{
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
	public function __construct ($name)
	{
		$this->name = $name;
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

		$filenames = scandir(\GitData\GitData::$root . '/packages/' . $this->name);

		foreach ($filenames as $filename)
		{
			if (!preg_match('/^release-(.+)\.json$/', $filename, $match))
			{
				continue;
			}

			$release = PackageRelease::find_by_package_and_version($this->name,
				$match[1]);

			if ($release === null)
			{
				continue;
			}

			$releases[] = $release;
		}

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
		$filenames = scandir(\GitData\GitData::$root . '/packages/' . $this->name);
		$latest = null;

		foreach ($filenames as $filename)
		{
			if (!preg_match('/^release-(.+)\.json$/', $filename, $match))
			{
				continue;
			}

			if ($latest === null)
			{
				$latest = $match[1];
				continue;
			}

			if (strcmp($latest, $match[1]) < 0)
			{
				$latest = $match[1];
			}
		}

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
		if (!is_dir(\GitData\GitData::$root . '/packages/' . $name))
		{
			return null;
		}

		try
		{
			return new Package($name);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
