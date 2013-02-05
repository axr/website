<?php

namespace GitData;

class GitData
{
	public static $root = null;
	public static $version = '0';
	public static $repo = null;

	public static function initialize ($root)
	{
		self::$root = $root;
		self::$version = file_get_contents(self::$root . '/.git/refs/heads/master');
		self::$repo = new Git\Repository($root);
	}
}

class Autoloader
{
	protected static $classes = array(
		'GitData\\Git\\Commit' => '/git/commit.php',
		'GitData\\Git\\File' => '/git/file.php',
		'GitData\\Git\\Repository' => '/git/repository.php',
		'GitData\\Asset' => '/asset.php',
		'GitData\\Exceptions\\EntityInvalid' => '/exceptions/entity_invalid.php',
		'GitData\\Model' => '/model.php',
		'GitData\\Models\\Page' => '/models/page.php',
		'GitData\\Models\\WikiPage' => '/models/wiki_page.php'
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