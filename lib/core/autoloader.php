<?php

namespace Core;

class Autoloader
{
	protected static $classes = array(
		'Cache' => '/cache.php',
		'Config' => '/config.php',
		'Core\\Benchmark' => '/benchmark.php',
		'Core\\Cache' => '/cache.php',
		'Core\\Config' => '/config.php',
		'Core\\Controller' => '/controller.php',
		'Core\\Controller' => '/controller.php',
		'Core\\Controller' => '/controller.php',
		'Core\\Controller' => '/controller.php',
		'Core\\Exceptions\\URLReadonly' => '/exceptions/url_readonly.php',
		'Core\\Exceptions\\HTTPAjaxException' => '/exceptions/http_ajax.php',
		'Core\\Exceptions\\MemcacheFailure' => '/exceptions/memcache_failure.php',
		'HTTPException' => '/http_exception.php',
		'Minify' => '/minify.php',
		'Router' => '/router.php',
		'RSRC' => '/rsrc.php',
		'URL' => '/url.php'
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
