<?php

require_once(SHARED . '/lib/core/rsrc.php');
require_once(SHARED . '/lib/core/minify.php');
require_once(SHARED . '/lib/mustache.php/Mustache.php');

class Controller
{
	/**
	 * View variables are kept here
	 *
	 * @var StdClass
	 */
	protected $view;

	/**
	 * Resource loader
	 *
	 * @var RSRC
	 */
	public $rsrc;

	/**
	 */
	public $tabs = array();

	/**
	 */
	public $breadcrumb = array();

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$that = $this;

		$this->view = new StdClass();
		$this->rsrc = new RSRC();

		$this->view->_rsrc_styles = function () use ($that)
		{
			return $that->rsrc->getStylesHTML();
		};

		$this->view->_rsrc_scripts = function () use ($that)
		{
			return $that->rsrc->getScriptsHTML();
		};

		$this->view->_breadcrumb_html = function () use ($that)
		{
			$mustache = new Mustache();
			$template = file_get_contents(SHARED . '/views/layout_breadcrumb.html');

			return $mustache->render($template, array(
				'has' => count($that->breadcrumb) > 0,
				'breadcrumb' => $that->breadcrumb
			));
		};

		$this->view->_tabs_html = function () use ($that)
		{
			$mustache = new Mustache();
			$template = file_get_contents(SHARED . '/views/layout_tabs.html');

			// Only one tab, and it's active == no tabs
			if (count($that->tabs) === 1 &&
				array_key_or($that->tabs[0], 'current', false) === true)
			{
				return;
			}

			return $mustache->render($template, array(
				'has' => count($that->tabs) > 0,
				'tabs' => $that->tabs
			));
		};

		$this->view->_year = date('Y');

		$this->view->_rsrc_root = Config::get('/shared/rsrc_url');
		$this->view->_www_url = Config::get('/shared/www_url');
		$this->view->_wiki_url = Config::get('/shared/wiki_url');

		$this->view->_app_vars = json_encode(array(
			'rsrc_root' => Config::get('/shared/rsrc_url'),
			'rsrc_prod' => Config::get('/shared/rsrc/prod'),
			'ga_account' => Config::get('/www/ga_account'),
			'rsrc_bundles' => $this->rsrc->getBundlesInfo()
		));

		$this->view->_POST = $_POST;
		$this->view->_GET = $_GET;

		$this->tabs = array();
		$this->breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => '/'
			)
		);
	}

	/**
	 * Initialize callback
	 */
	public function initialize ()
	{
	}

	/**
	 * Render a view
	 */
	protected function renderView ($viewPath)
	{
		$layoutPath = SHARED . '/views/layout.html';
		$explode = explode('.', $viewPath);
		$extension = end($explode);
		$viewHTML = $viewPath;
		$layoutHTML = '{{{_content}}}';

		if (file_exists($viewPath))
		{
			$viewHTML = file_get_contents($viewPath);
		}

		if (file_exists($layoutPath))
		{
			$layoutHTML = file_get_contents($layoutPath);
		}

		$mustache = new Mustache();

		$this->view->_content = $mustache->render($viewHTML, $this->view);
		$out = $mustache->render($layoutHTML, $this->view);
		
		return ($extension === 'html') ? Minify::html($out) : $out;
	}

	/**
	 * Render just the view. Don't wrap it with the layout
	 */
	public function renderViewOnly ($file, $minify = false)
	{
		if (!file_exists($file))
		{
			return false;
		}

		$explode = explode('.', $file);
		$extension = end($explode);

		$template = file_get_contents($file);

		$mustache = new Mustache();
		$out = $mustache->render($template, $this->view);

		return ($minify && $extension === 'html') ? Minify::html($out) : $out;
	}

	/**
	 * Redirect
	 *
	 * @param string $location
	 * @param int $code either 301 or null
	 */
	public function redirect ($location, $code = null)
	{
		if ($code === 301)
		{
			header('HTTP/1.1 301 Moved Permanently');
		}

		header('Location: ' . $location);
	}
}

