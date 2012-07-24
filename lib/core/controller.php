<?php

require_once(SHARED . '/lib/core/rsrc.php');
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
		return $mustache->render($layoutHTML, $this->view);
	}
}

