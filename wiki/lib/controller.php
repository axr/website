<?php

namespace Wiki;

require_once(SHARED . '/lib/axr/controller.php');

class Controller extends \AXR\Controller
{
	public function __construct ()
	{
		parent::__construct();

		// Load default resources
		$this->rsrc->load_bundles_file(SHARED . '/www/bundles.json');
		$this->rsrc->load_bundles_file(ROOT . '/bundles.json');
		$this->rsrc->load_bundle('css/bundle_shared.css');
		$this->rsrc->load_bundle('css/bundle_wiki.css');
		$this->rsrc->load_bundle('js/bundle_shared.js');
		$this->rsrc->load_bundle('js/bundle_wiki.js');

		// Set default breadcrumb
		$this->breadcrumb->push('Home', \Config::get()->url->www);
		$this->breadcrumb->push('Wiki', \Config::get()->url->wiki);
	}
}
