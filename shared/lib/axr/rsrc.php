<?php

class RSRC
{
	/**
	 * Styles
	 */
	private static $styles = array();

	/**
	 * Scripts
	 */
	private static $scripts = array();

	/**
	 * Add a style to the page
	 *
	 * @param string $path
	 * @param mixed[] $args
	 */
	public static function loadStyle ($path, $args = array())
	{
		self::$styles[$path] = $args;
	}

	/**
	 * Add a script to the page
	 *
	 * @param string $path
	 * @param mixed[] $args
	 */
	public static function loadScript ($path, $args = array())
	{
		self::$scripts[$path] = $args;
	}

	/**
	 * Add a bundle to the page
	 *
	 * @param string $bundle
	 */
	public static function loadBundle ($bundleName)
	{
		$bundles = self::getBundlesInfo();

		if (!isset($bundles->$bundleName))
		{
			return;
		}

		$bundle = $bundles->$bundleName;

		if (Config::get('/shared/rsrc/prod') === true)
		{
			$file = Config::get('/shared/rsrc_url') . '/' . $bundleName;

			if ($bundle->type === 'css')
			{
				self::loadStyle($file);
			}
			else if ($bundle->type === 'js')
			{
				self::loadScript($file);
			}

			return;
		}

		foreach ($bundle->files as $file)
		{
			if (preg_match('/^\{.+?\}/', $file))
			{
				$file = str_replace('{DRUPAL}',
					Config::get('/shared/www_url'), $file);
			}
			else
			{
				$file = Config::get('/shared/rsrc_url') . '/' . $file;
			}

			if ($bundle->type === 'css')
			{
				self::loadStyle($file);
			}
			else if ($bundle->type === 'js')
			{
				self::loadScript($file);
			}
		}
	}

	/**
	 * Get the HTML to insert styles into the layout
	 *
	 * @return string
	 */
	public static function getStylesHTML ()
	{
		$html = '';

		foreach (self::$styles as $path => $args)
		{
			if ($path[0] == '/')
			{
				$path = Config::get('/shared/rsrc_url') . '/' . $path;
			}

			$html .= '<link type="text/css" rel="stylesheet" ';
			$html .= 'href="' . $path . '" ';
			
			if (isset($args['media']))
			{
				$html .= 'media="' . $args['media'] . '" ';
			}

			$html .= '/>';
		}

		return $html;
	}

	/**
	 * Get the HTML to insert scripts into the layout
	 *
	 * @return string
	 */
	public static function getScriptsHTML ()
	{
		$html = '';

		foreach (self::$scripts as $path => $args)
		{
			if ($path[0] == '/')
			{
				$path = Config::get('/shared/rsrc_url') . '/' . $path;
			}

			$html .= '<script ';
			$html .= 'src="' . $path . '" ';
			
			if (isset($args['type']))
			{
				$html .= 'type="' . $args['type'] . '" ';
			}

			$html .= '></script>';
		}

		return $html;
	}

	/**
	 * Get bundles info
	 *
	 * @return string
	 */
	public static function getBundlesInfo ()
	{
		static $bundles;

		if (is_object($bundles))
		{
			return $bundles;
		}

		$bundles = file_get_contents(SHARED . '/bundles.json');
		$bundles = json_decode($bundles);

		if (!is_object($bundles))
		{
			$bundles = new StdClass();
		}

		return $bundles;
	}
}

