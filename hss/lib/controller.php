<?php

namespace Hssdoc;

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
		$this->rsrc->loadBundle('css/bundle_hssdoc.css');
		$this->rsrc->loadScript('https://cdnjs.cloudflare.com/ajax/libs/zepto/1.0rc1/zepto.min.js');
		$this->rsrc->loadBundle('js/bundle_shared.js');
		$this->rsrc->loadBundle('js/bundle_hssdoc.js');

		$this->breadcrumb[] = array(
			'name' => 'HSS documentation',
			'link' => \Config::get('/shared/hssdoc_url')
		);
	}
}
