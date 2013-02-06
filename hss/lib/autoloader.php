<?php

namespace Hssdoc;

class Autoloader
{
	protected static $classes = array(
		'Hssdoc\\Controller' => '/lib/controller.php',
		'Hssdoc\\HomeController' => '/controllers/home/home.php',
		'Hssdoc\\ObjectController' => '/controllers/object/object.php',
		'Hssdoc\\Sidebar' => '/lib/sidebar.php'
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
			require_once(dirname(__FILE__) . '/..' . self::$classes[$class]);
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
