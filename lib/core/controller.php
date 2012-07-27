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

		$this->view->_rsrc_root = Config::get('/shared/rsrc_url');
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
		$viewHTML = 'View not found';
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
}

