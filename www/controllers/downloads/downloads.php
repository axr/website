<?php

namespace WWW;

require_once(SHARED . '/lib/axr_releases.php');

class DownloadsController extends Controller
{
	public function run ()
	{
		$releases_browser = new \AXRReleases(\Config::get('/www/downloads/repo/browser'));

		$this->view->_title = 'Downloads';
		$this->breadcrumb[] = array(
			'name' => 'Downloads'
		);

		$this->view->release_groups = array(
			array(
				'name' => 'AXR Browser',
				'releases' => self::get_releases($releases_browser)
			)
		);

		// Remove empty release groups
		for ($i = 0, $c = count($this->view->release_groups); $i < $c; $i++)
		{
			if (count($this->view->release_groups[$i]['releases']) === 0)
			{
				unset($this->view->release_groups[$i]);
			}
		}

		echo $this->renderView(ROOT . '/views/downloads.html');
	}

	private static function get_releases ($releases)
	{
		$out = array();
		$data = (array) $releases->get_releases();

		ksort($data);
		$data = array_reverse($data);

		foreach ($data as $version => $rel)
		{
			$rel->windows = isset($rel->windows) ? $rel->windows : array();
			$rel->osx = isset($rel->osx) ? $rel->osx : array();
			$rel->linux = isset($rel->linux) ? $rel->linux : array();
			$rel->src = isset($rel->src) ? $rel->src : array();

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
					),
					array(
						'os' => 'src',
						'files' => $rel->src
					)
				)
			);
		}

		return $out;
	}
}
