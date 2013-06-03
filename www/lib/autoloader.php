<?php

namespace WWW;

class Autoloader
{
	protected static $classes = array(
		'WWW\\Controller' => '/lib/controller.php',
		'WWW\\ViewController' => '/controllers/view/view.php',
		'WWW\\PageController' => '/controllers/page/page.php',
		'WWW\\HomeController' => '/controllers/home/home.php',
		'WWW\\GitDataController' => '/controllers/gitdata/gitdata.php',
		'WWW\\DownloadsController' => '/controllers/downloads/downloads.php',
		'WWW\\AjaxController' => '/controllers/ajax/ajax.php',
		'WWW\\DisqusCommentsView' => '/lib/disqus_comments_view.php'
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
