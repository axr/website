<?php

require_once(SHARED . '/lib/core/controller.php');

class WWWController extends Controller
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
			$this->view->{'g/user'} = new StdClass();
			
			$this->view->{'g/user'}->id = Session::get('/user/id');
			$this->view->{'g/user'}->name = 'User';
			$this->view->{'g/user'}->url = '#';

			$this->view->{'g/user'}->links = array(
				array(
					'href' => '/auth/logout?continue=' . rawurlencode(Router::getUrl()->path),
					'text' => 'Log out'
				)
			);
		}
	}
}
