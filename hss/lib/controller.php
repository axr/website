<?php

namespace Hssdoc;

require_once(SHARED . '/lib/axr/controller.php');

class Controller extends \AXR\Controller
{
	public function __construct ()
	{
		parent::__construct();

		// Load default resources
		$this->rsrc->load_bundle('css/bundle_shared.css');
		$this->rsrc->load_bundle('css/bundle_hssdoc.css');
		$this->rsrc->load_bundle('js/bundle_shared.js');
		$this->rsrc->load_bundle('js/bundle_hssdoc.js');

		// Set default breadcrumb
		$this->breadcrumb->push('Home', \Config::get()->url->www);
		$this->breadcrumb->push('HSS documentation', \Config::get()->url->hss);
	}
}
