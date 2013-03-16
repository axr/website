<?php

class RSRC
{
	/**
	 * Styles
	 */
	private $styles = array();

	/**
	 * Scripts
	 */
	private $scripts = array();

	/**
	 * Add a style to the page
	 *
	 * @param string $path
	 * @param mixed[] $args
	 */
	public function loadStyle ($path, $args = array())
	{
		$this->styles[$path] = $args;
	}

	/**
	 * Add a script to the page
	 *
	 * @param string $path
	 * @param mixed[] $args
	 */
	public function loadScript ($path, $args = array())
	{
		$this->scripts[$path] = $args;
	}

	/**
	 * Add a bundle to the page
	 *
	 * @param string $bundle
	 */
	public function loadBundle ($bundleName)
	{
		$bundles = $this->getBundlesInfo();

		if (!isset($bundles->$bundleName))
		{
			return;
		}

		$bundle = $bundles->$bundleName;

		if (Config::get()->prod === true)
		{
			$file = Config::get()->url->rsrc . '/' . $bundleName;

			if ($bundle->type === 'css')
			{
				$this->loadStyle($file);
			}
			else if ($bundle->type === 'js')
			{
				 $this->loadScript($file);
			}

			return;
		}

		foreach ($bundle->files as $file)
		{
			$file = Config::get()->url->rsrc . '/' . $file;

			if ($bundle->type === 'css')
			{
				$this->loadStyle($file);
			}
			else if ($bundle->type === 'js')
			{
				$this->loadScript($file);
			}
		}
	}

	/**
	 * Get the HTML to insert styles into the layout
	 *
	 * @return string
	 */
	public function getStylesHTML ()
	{
		$html = '';

		foreach ($this->styles as $path => $args)
		{
			if ($path[0] == '/')
			{
				$path = \Config::get()->url->rsrc . '/' . $path;
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
	public function getScriptsHTML ()
	{
		$html = '';

		foreach ($this->scripts as $path => $args)
		{
			if ($path[0] == '/')
			{
				$path = Config::get()->url->rsrc . '/' . $path;
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
	public function getBundlesInfo ()
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

