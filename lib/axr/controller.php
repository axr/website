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

		$this->view->{'g/config'} = \Config::get();

		$this->view->{'g/app_vars'} = (object) array(
			'hssdoc_url' => \Config::get()->url->hss,
			'www_url' => \Config::get()->url->www,
			'wiki_url' => \Config::get()->url->wiki,
			'version' => \Config::get()->version,

			'rsrc_root' => (string) \URL::create(\Config::get()->url->rsrc),
			'ga_accounts' => \Config::get()->ga_accounts
		);

		$this->view->{'g/code_version'} = substr(\Config::get()->version, 0, 7);
		$this->view->{'g/data_version'} = substr(\GitData\GitData::$version, 0, 7);
		$this->view->{'g/code_version_full'} = \Config::get()->version;
		$this->view->{'g/data_version_full'} = \GitData\GitData::$version;

		$this->view->{'g/year'}  = date('Y');
		$this->view->{'g/meta'} = new \StdClass();
		$this->view->{'g/social'} = \GitData\Models\GenericConfig::file('config.json')->social;

		$this->tabs = array();
		$this->breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => \Config::get()->url->www
			)
		);
	}
}
