<?php

namespace WWW;

class DownloadsController extends Controller
{
	public function run ()
	{
		$view = new \Core\View(ROOT . '/views/downloads.html');
		$view->cache_condition('data_version', \GitData\GitData::$version);

		$view->twig()->addFilter(new \Twig_SimpleFilter('filesize', function ($bytes)
		{
			$size = (int) $bytes;

			$table = array(
				$size < pow(1024, 4) => array(pow(1024, 3), 'GiB'),
				$size < pow(1024, 3) => array(pow(1024, 2), 'MiB'),
				$size < pow(1024, 2) => array(pow(1024, 1), 'KiB'),
				$size < 1024 => array(1, 'B'),
			);

			return number_format($size / $table[true][0], 2) . ' ' . $table[true][1];
		}));

		if (!$view->load_from_cache())
		{
			$view->release_groups = array(
				array(
					'key' => 'browser',
					'name' => 'AXR Browser',
					'releases' => self::get_releases(array('axr-browser'))
				),
				array(
					'key' => 'core',
					'name' => 'AXR Core',
					'releases' => self::get_releases(array(
						'axr-runtime',
						'libaxr', 'libaxr-doc', 'libaxr-dev',
						'axr', 'axr-doc', 'axr-devel'))
				)
			);

			// Remove empty release groups
			for ($i = 0, $c = count($view->release_groups); $i < $c; $i++)
			{
				if (count($view->release_groups[$i]['releases']) === 0)
				{
					unset($view->release_groups[$i]);
				}
			}
		}

		$this->breadcrumb->push('Downloads', '/downloads');

		$this->layout->title = 'Downloads';
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}

	private static function get_releases ($packages)
	{
		$cache_key = '/www/releases/' . md5(implode(',', $packages));

		$files = \Cache::get($cache_key);
		if ($files !== null)
		{
			return $files;
		}

		$files = array();

		foreach ($packages as $package_name)
		{
			$package = \GitData\Models\Package::find_by_name($package_name);

			if ($package === null)
			{
				continue;
			}

			$releases = $package->get_all_releases();

			foreach ($releases as $release)
			{
				if (!isset($files[$release->version]))
				{
					$files[$release->version] = array(
						'pkggroups' => array(
							'windows' => null,
							'osx' => null,
							'debian' => null,
							'rpm' => null,
							'linux' => null,
							'src' => null
						)
					);
				}

				foreach ($release->files as $file)
				{
					$file->package = $package;

					switch ($file->type)
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

					if (!isset($files[$release->version]['pkggroups'][$group_key]))
					{
						$files[$release->version]['pkggroups'][$group_key] = array(
							'group_key' => $group_key,
							'group_name' => $group_name,
							'files' => array()
						);
					}

					$files[$release->version]['pkggroups'][$group_key]['files'][] = $file;
				}
			}

			// Remove empty pkggroups
			foreach ($files[$release->version]['pkggroups'] as $key => $data)
			{
				if ($data === null)
				{
					unset($files[$release->version]['pkggroups'][$key]);
				}
			}
		}

		\Cache::set($cache_key, $files, array(
			'data_version' => 'current'
		));

		return $files;
	}
}
