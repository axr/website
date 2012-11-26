<?php

namespace WWW;

require_once(SHARED . '/lib/axr/controller.php');

class Controller extends \AXR\Controller
{
	public function __construct ()
	{
		parent::__construct();

		$this->rsrc->loadBundle('css/bundle_shared.css');
		$this->rsrc->loadBundle('css/bundle_www.css');
		$this->rsrc->loadScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
		$this->rsrc->loadBundle('js/bundle_shared.js');
		$this->rsrc->loadBundle('js/bundle_www.js');

		if (\Session::get('/user/is_auth'))
		{
			$this->view->{'g/user'} = new \StdClass();

			$this->view->{'g/user'}->id = \Session::get('/user/id');
			$this->view->{'g/user'}->name = 'User';
			$this->view->{'g/user'}->url = '#';

			$this->view->{'g/user'}->links = array(
				array(
					'href' => \Config::get('/shared/www_url')
						->copy()
						->path('/account'),
					'text' => 'Account'
				),
				array(
					'href' => \Config::get('/shared/www_url')
						->copy()
						->path('/auth/logout')
						->query('continue', (string) \Router::get_instance()->url),
					'text' => 'Log out'
				)
			);
		}

		$this->view->{'g/hide_login'} = true;

		$this->view->{'g/app_vars'}->site->url = (string) \Config::get('/shared/www_url');
		$this->view->{'g/app_vars'}->site->ga_account = \Config::get('/www/ga_account');
		$this->view->{'g/app_vars'}->site->app_id = 'www';
	}
}
