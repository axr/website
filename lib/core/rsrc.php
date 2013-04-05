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
		$this->styles[(string) $path] = $args;
	}

	/**
	 * Add a script to the page
	 *
	 * @param string $path
	 * @param mixed[] $args
	 */
	public function loadScript ($path, $args = array())
	{
		$this->scripts[(string) $path] = $args;
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
			$url = \URL::create(Config::get()->url->rsrc);
			$url->path .= '/' . $bundleName;

			if ($bundle->type === 'css')
			{
				$this->loadStyle($url);
			}
			else if ($bundle->type === 'js')
			{
				 $this->loadScript($url);
			}

			return;
		}

		foreach ($bundle->files as $file)
		{
			$url = \URL::create(Config::get()->url->rsrc);
			$url->path .= '/' . $file;

			if ($bundle->type === 'css')
			{
				$this->loadStyle($url);
			}
			else if ($bundle->type === 'js')
			{
				$this->loadScript($url);
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
			$url = $path;

			if ($path[0] === '/' && $path[1] !== '/')
			{
				$url = \URL::create(Config::get()->url->rsrc);
				$url->path .= '/' . $path;
			}

			$html .= '<link type="text/css" rel="stylesheet" ';
			$html .= 'href="' . $url . '" ';

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
			$url = $path;

			if ($path[0] === '/' && $path[1] !== '/')
			{
				$url = \URL::create(Config::get()->url->rsrc);
				$url->path .= '/' . $path;
			}

			$html .= '<script ';
			$html .= 'src="' . $url . '" ';

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

