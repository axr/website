<?php

namespace GitData\Models;

class PackageRelease extends \GitData\Model
{
	protected $attrs = array('package', 'version', 'core_version', 'files');
	protected $public = array();

	/**
	 * __construct
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		parent::__construct($info_file);

		$this->_cache_write_state();
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
			return PackageRelease::new_instance($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
