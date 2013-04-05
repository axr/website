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
	 * @param mixed $url
	 * @param mixed[] $args
	 */
	public function load_style ($url, array $args = array())
	{
		$this->styles[(string) $url] = $args;
	}

	/**
	 * Add a script to the page
	 *
	 * @param mixed $url
	 * @param mixed[] $args
	 */
	public function load_script ($url, array $args = array())
	{
		$this->scripts[(string) $url] = $args;
	}

	/**
	 * Add a bundle to the page
	 *
	 * @param string $bundle
	 */
	public function load_bundle ($bundle_name)
	{
		$bundles = $this->get_bundles_info();

		if (!isset($bundles->$bundle_name))
		{
			return;
		}

		$bundle = $bundles->$bundle_name;
		$files = array();

		if (Config::get()->prod === true)
		{
			$files[] = $bundle_name;
		}
		else
		{
			$files = (array) $bundle->files;
		}

		foreach ($files as $file)
		{
			$url = \URL::create(Config::get()->url->rsrc);
			$url->path .= '/' . $file;

			if ($bundle->type === 'css')
			{
				$this->load_style($url);
			}
			else if ($bundle->type === 'js')
			{
				$this->load_script($url);
			}
		}
	}

	/**
	 * Get the HTML to insert styles into the layout
	 *
	 * @return string
	 */
	public function get_styles_html ()
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
	public function get_scripts_html ()
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
	 * @todo cache this
	 * @return string
	 */
	public function get_bundles_info ()
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

