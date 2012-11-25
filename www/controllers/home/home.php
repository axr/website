<?php

namespace WWW;

require_once(SHARED . '/lib/axr_releases.php');

class HomeController extends Controller
{
	public function run ()
	{
		$releases = new \AXRReleases(\Config::get('/www/downloads/repo/browser'));

		$oses = array(
			'osx' => 'OSX',
			'linux' => 'Linux',
			'win' => 'Windows'
		);

		$release = $releases->get_releases_for_home();

		if (is_object($release))
		{
			$this->view->release = clone $release;
			$this->view->release->os = isset($oses[$release->os]) ?
				$oses[$release->os] : $release->os;
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
