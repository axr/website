<?php

namespace WWW;

class Autoloader
{
	protected static $classes = array(
		'WWW\\Controller' => '/lib/controller.php',
		'WWW\\ViewController' => '/controllers/view/view.php',
		'WWW\\PageController' => '/controllers/page/page.php',
		'WWW\\HssdocController' => '/controllers/hssdoc/hssdoc.php',
		'WWW\\HomeController' => '/controllers/home/home.php',
		'WWW\\GetInvolvedController' => '/controllers/get_involved/get_involved.php',
		'WWW\\DownloadsController' => '/controllers/downloads/downloads.php',
		'WWW\\AuthController' => '/controllers/auth/auth.php',
		'WWW\\AjaxController' => '/controllers/ajax/ajax.php',
		'WWW\\AdminController' => '/controllers/admin/admin.php',
		'WWW\\UserOID' => '/models/user_oid.php',
		'WWW\\User' => '/models/user.php',
		'WWW\\Page' => '/models/page.php',
		'WWW\\HssdocValue' => '/models/hssdoc_value.php',
		'WWW\\HssdocProperty' => '/models/hssdoc_property.php',
		'WWW\\HssdocObject' => '/models/hssdoc_object.php'
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
			require_once(ROOT . self::$classes[$class]);
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
