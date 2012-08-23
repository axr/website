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

		$changelog_raw = null;
		$changelog = array();

		if (is_object($release))
		{
			$release->os_str = isset($oses[$release->os]) ?
				$oses[$release->os] : $release->os;

			$this->view->release = $release;
			$this->view->has_release = true;

			$this->view->full_changelog_url = 'http://github.com/' .
				Config::get('/www/downloads/releases_repo') .
				'/commits/v' . $release->version. '-stable';

			$changelog_raw = $releases->getChangelog($release->version);
		}

		if (is_array($changelog_raw))
		{
			foreach ($changelog_raw as $change)
			{
				$changelog[] = array(
					'change' => $change
				);
			}

			$this->view->changelog = $changelog;
			$this->view->has_changelog = count($changelog) > 0;
		}

		echo $this->renderView(ROOT . '/views/home.html');
	}
}

