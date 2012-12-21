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
		$this->view->{'g/app_vars'}->session->is_logged = User::current()->is_logged();

		$usermenu = null;
		$search = null;

		// Create the user menu
		if (User::current()->is_logged())
		{
			$usermenu = $this->render_simple_view(SHARED . '/views/layout_usermenu.html',
				(object) array(
				'show_usermenu' => true,
				'user' => array(
					'id' => User::current()->id,
					'name' => User::current()->name_short,
					'url' => \URL::create()
						->from_string(\Config::get('/shared/www_url'))
						->path('/account')
				),
				'links' => array(
					array(
						'text' => 'Account',
						'href' => \URL::create()
							->from_string(\Config::get('/shared/www_url'))
							->path('/account')
					),
					array(
						'text' => 'Log out',
						'href' => \URL::create()
							->from_string(\Config::get('/shared/www_url'))
							->path('/auth/logout')
							->query('continue', (string) \Router::get_instance()->url)
					)
				)
			));
		}

		// Create the search box
		// $search = $this->render_simple_view(SHARED . '/views/layout_search.html', (object) array(
		// 	'placeholder' => 'Search the website'
		// ));

		$this->view->{'g/html_secondary'} = $usermenu . $search;
	}
}
