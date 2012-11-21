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
			$mustache = new \Mustache\Renderer();
			$template = file_get_contents(SHARED . '/views/layout_breadcrumb.html');

			return $mustache->render($template, array(
				'has' => count($that->breadcrumb) > 0,
				'breadcrumb' => $that->breadcrumb
			));
		};

		$this->view->{'g/tabs/html'} = function () use ($that)
		{
			$mustache = new \Mustache\Renderer();
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

		$this->view->{'g/app_vars'} = json_encode(array(
			'/shared/hssdoc_url' => \Config::get('/shared/hssdoc_url'),
			'/shared/www_url' => \Config::get('/shared/www_url'),
			'rsrc_root' => \Config::get('/shared/rsrc_url'),
			'rsrc_prod' => \Config::get('/shared/rsrc/prod'),
			'ga_account' => \Config::get('/www/ga_account'),
			'rsrc_bundles' => $this->rsrc->getBundlesInfo()
		));

		$this->view->{'g/year'}  = date('Y');
		$this->view->{'g/meta'} = new \StdClass();
		$this->view->{'g/url_login/label'} = 'Login';

		$this->view->{'g/rsrc_root'} = \Config::get('/shared/rsrc_url');
		$this->view->{'g/www_url'} = \Config::get('/shared/www_url');
		$this->view->{'g/wiki_url'}  = \Config::get('/shared/wiki_url');

		$this->tabs = array();
		$this->breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => '/'
			)
		);
	}
}
