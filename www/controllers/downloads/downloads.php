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

		$this->view->release_types = array(
			array(
				'name' => 'Prototype',
				'releases' => self::get_releases($releases, 'stable')
			),
			array(
				'name' => 'Developer releases',
				'releases' => self::get_releases($releases, 'dev')
			)
		);

		echo $this->renderView(ROOT . '/views/downloads.html');
	}

	private static function get_releases ($releases, $type)
	{
		$out = array();
		$data = (array) $releases->get_releases_by_type($type);

		ksort($data);
		$data = array_reverse($data);

		foreach ($data as $version => $rel)
		{
			$rel->windows = isset($rel->windows) ? $rel->windows : array();
			$rel->osx = isset($rel->osx) ? $rel->osx : array();
			$rel->linux = isset($rel->linux) ? $rel->linux : array();

			$out[] = array(
				'version' => $version,
				'oses' => array(
					array(
						'os' => 'windows',
						'files' => $rel->windows
					),
					array(
						'os' => 'osx',
						'files' => $rel->osx
					),
					array(
						'os' => 'linux',
						'files' => $rel->linux
					)
				)
			);
		}

		return $out;
	}
}
