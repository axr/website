<?php

namespace WWW;

require_once(SHARED . '/lib/axr/controller.php');

class Controller extends \AXR\Controller
{
	public function __construct ()
	{
		parent::__construct();

		$that = $this;

		$this->view->{'g/html_head'} = function () use ($that)
		{
			return $that->rsrc->getStylesHTML();
		};

		$this->view->{'g/html_bottom'} = function () use ($that)
		{
			return $that->rsrc->getScriptsHTML();
		};

		// Load some default resources
		$this->rsrc->loadBundle('css/bundle_shared.css');
		$this->rsrc->loadBundle('css/bundle_www.css');
		$this->rsrc->loadScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
		$this->rsrc->loadBundle('js/bundle_shared.js');
		$this->rsrc->loadBundle('js/bundle_www.js');

		// Set application variables
		$this->view->{'g/app_vars'}->site->url = (string) \Config::get('/shared/www_url');
		$this->view->{'g/app_vars'}->site->ga_account = \Config::get('/www/ga_account');
		$this->view->{'g/app_vars'}->site->app_id = 'www';
	}
}
