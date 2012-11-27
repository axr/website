<?php

namespace WWW;

require_once(SHARED . '/lib/axr/gh_repository.php');

class DownloadsController extends Controller
{
	public function run ()
	{
		$this->view->_title = 'Downloads';
		$this->breadcrumb[] = array(
			'name' => 'Downloads'
		);

		$this->view->release_groups = array(
			array(
				'name' => 'AXR Browser',
				'releases' => self::get_releases(\Config::get('/www/downloads/repo/browser'))
			),
			array(
				'name' => 'AXR Core',
				'releases' => self::get_releases(\Config::get('/www/downloads/repo/core'))
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

	/**
	 * @todo Cache this monster
	 */
	private static function get_releases ($repo_name)
	{
		$repo = new \AXR\GHRepository($repo_name);
		$repo->load();

		$releases = $repo->get_releases();

		// TODO: Order the releases

		$out = array();

		foreach ($releases as $version => $release)
		{
			$packages = array();

			if (!isset($release->packages))
			{
				continue;
			}

			foreach ($release->packages as $package_name => $package)
			{
				if (!isset($package->files))
				{
					continue;
				}

				$oses = array(
					'windows' => array(
						'os' => 'windows',
						'files' => array()
					),
					'osx' => array(
						'os' => 'osx',
						'files' => array()
					),
					'linux' => array(
						'os' => 'linux',
						'files' => array()
					),
					'source' => array(
						'os' => 'src',
						'files' => array()
					)
				);

				foreach ($package->files as $file)
				{
					if (!isset($oses[$file->os]))
					{
						continue;
					}

					if ($file->arch === 'osx_uni')
					{
						$file->arch = 'universal';
					}

					$oses[$file->os]['files'][] = $file;
				}

				$packages[] = array(
					'name' => $package_name,
					'oses' => $oses
				);
			}

			$out[] = array(
				'version' => $release->version,
				'packages' => $packages
			);
		}

		return $out;
	}
}
