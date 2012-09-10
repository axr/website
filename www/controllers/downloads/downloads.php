<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(SHARED . '/lib/axr_releases.php');

class DownloadsController extends WWWController
{
	public function run ()
	{
		$releases = new AXRReleases(Config::get('/www/downloads/releases_repo'));
		$data = $releases->get_releases();

		$this->view->_title = 'Downloads';
		$this->breadcrumb[] = array(
			'name' => 'Downloads'
		);

		$this->view->releases = $this->filter_releases_for_view($data);
		$this->view->has_releases = count($this->view->releases) > 0;

		echo $this->renderView(ROOT . '/views/downloads.html');
	}

	/**
	 * Reformat data from AXRReleases so it can be passed to the view
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	private function filter_releases_for_view ($data)
	{
		$out = array();

		$data = (array) clone $data;
		ksort($data);
		$data = array_reverse($data);

		foreach ($data as $version => $rel)
		{
			$rel->win = isset($rel->win) ? $rel->win : array();
			$rel->osx = isset($rel->osx) ? $rel->osx : array();
			$rel->linux = isset($rel->linux) ? $rel->linux : array();

			$out[] = array(
				'version' => $version,
				'oses' => array(
					array(
						'os' => 'win',
						'files' => $rel->win,
						'has_files' => count($rel->win) > 0
					),
					array(
						'os' => 'osx',
						'files' => $rel->osx,
						'has_files' => count($rel->osx) > 0
					),
					array(
						'os' => 'linux',
						'files' => $rel->linux,
						'has_files' => count($rel->linux) > 0
					)
				)
			);
		}

		return $out;
	}
}

