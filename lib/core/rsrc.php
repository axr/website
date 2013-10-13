<?php

class RSRC
{
	private $bundles;

	/**
	 * Styles
	 */
	private $styles = array();

	/**
	 * Scripts
	 */
	private $scripts = array();

	public function __construct ()
	{
		$bundles = new StdClass;
	}

	/**
	 * Load a bundles.json file
	 *
	 * @param string $path
	 */
	public function load_bundles_file ($path)
	{
		if (!file_exists($path))
		{
			return;
		}

		$bundles = file_get_contents($path);
		$bundles = json_decode($bundles);

		if (is_object($bundles))
		{
			$this->bundles = (object) array_merge((array) $this->bundles, (array) $bundles);
		}
	}

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
		if (!isset($this->bundles->$bundle_name))
		{
			return;
		}

		$bundle = $this->bundles->$bundle_name;
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
			if (Config::get()->prod === true)
			{
				$url = \URL::create(Config::get()->url->rsrc);
				$url->path .= '/' . $file;
			}
			else
			{
				if (!isset(Config::get()->url->{$bundle->owner}))
				{
					continue;
				}

				$url = \URL::create(Config::get()->url->{$bundle->owner});
				$url->path .= '/static/' . $file;
			}

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
}

