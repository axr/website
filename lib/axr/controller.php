<?php

namespace AXR;

require_once(SHARED . '/lib/core/controller.php');

class Controller extends \Core\Controller
{
	public function __construct ()
	{
		parent::__construct();

		$that = $this;

		$this->view->{'g/breadcrumb/html'} = function () use ($that)
		{
			return $that->render_simple_view(SHARED . '/views/layout_breadcrumb.html',
				(object) array(
				'has' => count($that->breadcrumb) > 0,
				'breadcrumb' => $that->breadcrumb
			));
		};

		$this->view->{'g/tabs/html'} = function () use ($that)
		{
			// Only one tab, and it's active == no tabs
			if (count($that->tabs) === 1 &&
				array_key_or($that->tabs[0], 'current', false) === true)
			{
				return;
			}

			return $that->render_simple_view(SHARED . '/views/layout_tabs.html',
				(object) array(
				'has' => count($that->tabs) > 0,
				'tabs' => $that->tabs
			));
		};

		$this->view->{'g/app_vars'} = (object) array(
			'hssdoc_url' => (string) \Config::get('/shared/hssdoc_url'),
			'www_url' => (string) \Config::get('/shared/www_url'),
			'wiki_url' => (string) \Config::get('/shared/wiki_url'),
			'version' => \Config::get('/shared/version'),

			'rsrc_root' => (string) \Config::get('/shared/rsrc_url'),
			'rsrc_bundles' => $this->rsrc->getBundlesInfo(),

			'ga_accounts' => \Config::get('/shared/ga_accounts')
		);

		$this->view->{'g/year'}  = date('Y');
		$this->view->{'g/meta'} = new \StdClass();
		$this->view->{'g/social'} = \GitData\Models\GenericConfig::file('config.json')->social;

		$this->view->{'g/rsrc_root'} = (string) \Config::get('/shared/rsrc_url');
		$this->view->{'g/www_url'} = (string) \Config::get('/shared/www_url');
		$this->view->{'g/wiki_url'}  = (string) \Config::get('/shared/wiki_url');
		$this->view->{'g/hssdoc_url'}  = (string) \Config::get('/shared/hssdoc_url');

		$this->tabs = array();
		$this->breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => \Config::get('/shared/www_url')
			)
		);
	}
}
