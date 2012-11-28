<?php

namespace WWW;

require_once(SHARED . '/lib/axr/gh_repository.php');

class HomeController extends Controller
{
	public function run ()
	{
		$repo = new \AXR\GHRepository(\Config::get('/www/downloads/repo/browser'));
		$repo->load();

		$oses = array(
			'osx' => 'OSX',
			'linux' => 'Linux',
			'windows' => 'Windows'
		);

		$release = $repo->get_release('latest');

		if (is_object($release) &&
			isset($release->packages) &&
			isset($release->packages->{'axr-browser'}))
		{
			$release->_file = \AXR\GHRepository::choose_best_file($release->packages->{'axr-browser'}->files);

			if ($release->_file !== null)
			{
				$release->_file->_os = isset($oses[$release->_file->os]) ?
					$oses[$release->_file->os] : $release->_file->os;

				$this->view->release = $release;
			}
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
