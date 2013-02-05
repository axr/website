<?php

namespace Wiki;

class Autoloader
{
	protected static $classes = array(
		'Wiki\\Controller' => '/lib/controller.php',
		'Wiki\\PageController' => '/controllers/page/page.php',
		'Wiki\\ViewController' => '/controllers/view/view.php'
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
