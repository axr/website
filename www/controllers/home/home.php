<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(SHARED . '/lib/axr_releases.php');

class HomeController extends WWWController
{
	public function run ()
	{
		$releases = new AXRReleases(Config::get('/www/downloads/releases_repo'));

		$oses = array(
			'osx' => 'OSX',
			'linux' => 'Linux',
			'win' => 'Windows'
		);

		$release = $releases->getForHome();

		if (is_object($release))
		{
			$release->os_str = isset($oses[$release->os]) ?
				$oses[$release->os] : $release->os;
		}

		$this->view->_breadcrumb = false;
		$this->view->release = $release;
		$this->view->has_release = is_object($release);

		echo $this->renderView(ROOT . '/views/home.html');
	}
}

