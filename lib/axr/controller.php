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

		$this->view->{'g/app_vars'} = (object) array(
			'/shared/hssdoc_url' => (string) \Config::get('/shared/hssdoc_url'),
			'/shared/www_url' => (string) \Config::get('/shared/www_url'),
			'version' => \Config::get('/shared/version'),
			'rsrc' => (object) array(
				'root' => (string) \Config::get('/shared/rsrc_url'),
				'prod' => \Config::get('/shared/rsrc/prod'),
				'bundles' => $this->rsrc->getBundlesInfo()
			),
			'site' => (object) array(
				'url' => null,
				'ga_account' => null
			),
			'session' => (object) array(
				'is_logged' => false
			)
		);

		$this->view->{'g/year'}  = date('Y');
		$this->view->{'g/meta'} = new \StdClass();
		$this->view->{'g/url_login/label'} = 'Login';

		$this->view->{'g/rsrc_root'} = (string) \Config::get('/shared/rsrc_url');
		$this->view->{'g/www_url'} = (string) \Config::get('/shared/www_url');
		$this->view->{'g/wiki_url'}  = (string) \Config::get('/shared/wiki_url');

		$this->tabs = array();
		$this->breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => '/'
			)
		);
	}
}
