<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(ROOT . '/models/page.php');
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

		$release = $releases->get_for_home();

		if (is_object($release))
		{
			$release->os_str = isset($oses[$release->os]) ?
				$oses[$release->os] : $release->os;
			$release->date_str = date('Y-m-d', $release->date);

			$this->view->release = $release;
			$this->view->has_release = true;
			$this->view->has_changelog = count($release->changes) > 0;

			$this->view->full_changelog_url = 'http://github.com/' .
				Config::get('/www/downloads/releases_repo') .
				'/blob/master/CHANGELOG.md';
		}

		// Get blog posts
		$this->view->blog_posts = Page::all(array(
			'conditions' => array('ctype = ? AND published = 1', 'bpost'),
			'order' => 'ctime desc',
			'limit' => 5,
		));	

		echo $this->renderView(ROOT . '/views/home.html');
	}
}

