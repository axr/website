<?php

namespace WWW;

require_once(SHARED . '/lib/axr/gh_repository.php');
require_once(SHARED . '/lib/mustache_filters/filesize.php');

class DownloadsController extends Controller
{
	public function initialize ()
	{
		\Mustache\Filter::register(new \MustacheFilters\Filesize);
	}

	public function run ()
	{
		$this->view->_title = 'Downloads';
		$this->breadcrumb[] = array(
			'name' => 'Downloads'
		);

		$this->view->release_groups = array(
			array(
				'key' => 'browser',
				'name' => 'AXR Browser',
				'releases' => self::get_releases(\Config::get('/www/downloads/repo/browser'))
			),
			array(
				'key' => 'core',
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
		$out = array();

		foreach ($releases as $version => $release)
		{
			if (!isset($release->packages))
			{
				continue;
			}

			$out[$version] = array(
				'version' => $version,
				'pkggroups' => array()
			);

			foreach ($release->packages as $package_name => $package)
			{
				foreach ($package->files as $file)
				{
					switch ($file->ext)
					{
						case 'deb': $group_key = 'debian'; break;
						case 'rpm': $group_key = 'rpm'; break;
						default: $group_key = $file->os;
					}

					switch ($group_key)
					{
						case 'linux': $group_name = 'Linux'; break;
						case 'debian': $group_name = 'Debian'; break;
						case 'rpm': $group_name = 'RPM'; break;
						case 'osx': $group_name = 'OS X'; break;
						case 'windows': $group_name = 'Windows'; break;
						case 'src': $group_name = 'Source'; break;
						default: $group_name = $group_key;
					}

					if (!isset($out[$version]['pkggroups'][$group_key]))
					{
						$out[$version]['pkggroups'][$group_key] = array(
							'group_key' => $group_key,
							'group_name' => $group_name,
							'files' => array()
						);
					}

					if ($file->arch === 'osx_uni')
					{
						$file->arch = 'universal';
					}

					$out[$version]['pkggroups'][$group_key]['files'][] = $file;
				}
			}
		}

		return $out;
	}
}
