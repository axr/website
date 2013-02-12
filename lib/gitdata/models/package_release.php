<?php

namespace GitData\Models;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class PackageRelease extends \GitData\Model
{
	/**
	 * Name of the package that this release belongs to
	 *
	 * @var string
	 */
	public $package;

	/**
	 * Version of this release
	 *
	 * @var string
	 */
	public $version;

	/**
	 * List of files within this release
	 *
	 * @var string
	 */
	public $files;

	/**
	 * __construct
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		$info = json_decode($info_file->get_data());

		if (!is_object($info))
		{
			throw new \GitData\Exceptions\EntityInvalid(null);
		}

		foreach ($info as $key => $value)
		{
			if (property_exists(__CLASS__, $key))
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * Find a release by package name and version.
	 *
	 * @param string $package
	 * @param string $version
	 * @return \GitData\Models\PackageRelease
	 */
	public static function find_by_package_and_version ($package, $version)
	{
		$info_file = \GitData\GitData::$repo->get_file(
			'/packages/' . $package . '/release-' . $version . '.json');

		if ($info_file === null)
		{
			return null;
		}

		try
		{
			return new PackageRelease($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
