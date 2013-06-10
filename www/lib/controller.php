<?php

namespace WWW;

require_once(SHARED . '/lib/axr/controller.php');

class Controller extends \AXR\Controller
{
	public function __construct ()
	{
		parent::__construct();

		// Load default resources
		$this->rsrc->load_bundle('css/bundle_shared.css');
		$this->rsrc->load_bundle('css/bundle_rainbow.css');
		$this->rsrc->load_bundle('css/bundle_www.css');
		$this->rsrc->load_bundle('js/bundle_shared.js');
		$this->rsrc->load_bundle('js/bundle_www.js');

		// Set default breadcrumb
		$this->breadcrumb->push('Home', \Config::get()->url->www);
	}
}
