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

		$this->view->_app_vars = json_encode(array(
			'rsrc_root' => Config::get('/shared/rsrc_url'),
			'rsrc_prod' => Config::get('/shared/rsrc/prod'),
			'ga_account' => Config::get('/www/ga_account'),
			'rsrc_bundles' => $this->rsrc->getBundlesInfo()
		));

		if (Session::get('/user/is_auth'))
		{
			$this->view->_user = array(
				'id' => Session::get('/user/id')
			);

			$this->view->_url_profile =
			$this->view->_url_account = '/account';
			$this->view->_url_logout = '/auth/logout';
		}

		$this->view->_url_login = '/auth';
	}
}

