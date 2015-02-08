<?php

namespace GitData;

class GitData
{
	public static $repo = null;
	public static $head = null;
	public static $tree = null;
	public static $version = '0';

	public static function initialize ($path)
	{
		self::$repo = git_repository_open($path);
		self::$head = git_repository_head(self::$repo);
		self::$tree = git_reference_peel(self::$head, GIT_OBJ_TREE);
	}

	public static function commit ()
	{
		static $commit = null;

		if (!$commit)
		{
			$oid = git_reference_target(self::$head);
			$commit = git_commit_lookup(self::$repo, $oid);
		}

		return $commit;
	}
}

class Autoloader
{
	protected static $classes = array(
		'GitData\\Git\\File' => '/git/file.php',
		'GitData\\Asset' => '/asset.php',
		'GitData\\Compound' => '/compound.php',
		'GitData\\Content' => '/content.php',
		'GitData\\Exceptions\\EntityInvalid' => '/exceptions/entity_invalid.php',
		'GitData\\Model' => '/model.php',
		'GitData\\Models\\GenericConfig' => '/models/generic_config.php',
		'GitData\\Models\\HssdocProperty' => '/models/hssdoc_property.php',
		'GitData\\Models\\HssdocObject' => '/models/hssdoc_object.php',
		'GitData\\Models\\Package' => '/models/package.php',
		'GitData\\Models\\PackageRelease' => '/models/package_release.php',
		'GitData\\Models\\Page' => '/models/page.php',
		'GitData\\Models\\WikiPage' => '/models/wiki_page.php',
		'GitData\\Util' => '/util.php'
	);

	/**
	 * Load a class
	 *
	 * @param string $class
	 */
	public static function load ($class)
	{
		if (isset(self::$classes[$class]))
		{
			require_once(dirname(__FILE__) . '/' . self::$classes[$class]);
		}
	}

	/**
	 * Register the autoloader
	 */
	public static function register ()
	{
		spl_autoload_register(__CLASS__ . '::load');
	}
}

// Register the autoloader
Autoloader::register();
