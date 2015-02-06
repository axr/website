<?php

namespace GitData\Models;

class PackageRelease extends \GitData\Model
{
	protected $attrs = array('package', 'version', 'core_version', 'files');
	protected $public = array();

	/**
	 * __construct
	 */
	public function __construct ($info)
	{
		parent::__construct($info);
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
		$path = 'packages/' . $package . '/release-' . $version . '.json';
		$info = \GitData\Util::read_info($path);

		if ($info)
		{
			return new PackageRelease($info);
		}
	}
}
