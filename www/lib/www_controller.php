<?php

require_once(SHARED . '/lib/axr/controller.php');

class WWWController extends \AXR\Controller
{
	public function __construct ()
	{
		parent::__construct();

		$this->rsrc->loadBundle('css/bundle_shared.css');
		$this->rsrc->loadBundle('css/bundle_www.css');
		$this->rsrc->loadScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
		$this->rsrc->loadBundle('js/bundle_shared.js');
		$this->rsrc->loadBundle('js/bundle_www.js');

		if (Session::get('/user/is_auth'))
		{
			$this->view->{'g/user'} = array(
				'id' => Session::get('/user/id')
			);

			$this->view->{'g/url_profile'} =
			$this->view->{'g/url_account'} = '/account';
			$this->view->{'g/url_logout'} = '/auth/logout?continue=' .
				rawurlencode(Router::getUrl()->path);
		}

		$this->view->{'g/hide_login'} = true;

		$this->view->{'g/app_vars'}->site->url = (string) \Config::get('/shared/www_url');
		$this->view->{'g/app_vars'}->site->ga_account = \Config::get('/www/ga_account');
	}
}
