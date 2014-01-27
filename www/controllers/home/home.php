<?php

namespace WWW;

require_once(SHARED . '/lib/axr/pkgtools.php');

class HomeController extends Controller
{
	public function run ()
	{
		$view = new \Core\View(ROOT . '/views/home.html');

		$package = \GitData\Models\Package::find_by_name('axr-browser');
		$best_file = self::find_best_file($package);

		if ($best_file !== null)
		{
			$client_os = \AXR\Pkgtools::detect_os();
			$client_distro = \AXR\Pkgtools::detect_linux_distro();

			$os_str = \AXR\Pkgtools::os_to_human($client_os);

			if ($best_file->type !== '.tar.gz' && $client_distro !== null)
			{
				$os_str = \AXR\Pkgtools::distro_to_human($client_distro);
			}

			$view->release = array(
				'os_str' => $os_str,
				'version' => $best_file->_version,
				'filename' => $best_file->filename,
				'url' => $best_file->url
			);
		}

		// Get the data for the latest blog posts section
		{
			$all_posts = \GitData\Models\Page::get_blog_index();
			$posts = array();

			for ($i = 0; $i < 5; $i++)
			{
				if (!isset($all_posts[$i]))
				{
					break;
				}

				$posts[] = \GitData\Models\Page::find_by_path($all_posts[$i]->path);
			}

			$view->blog_posts = $posts;
		}

		$this->breadcrumb->clear();

		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}

	protected static function find_best_file (\GitData\Models\Package $package)
	{
		$releases = $package->get_all_releases();

		$client_os = \AXR\Pkgtools::detect_os();
		$client_arch = \AXR\Pkgtools::detect_arch();
		$client_pm_ext = \AXR\Pkgtools::get_pm_ext();

		$close_enough = null;

		foreach ($releases as $release)
		{
			foreach ($release->files as $file)
			{
				if ($file->os !== $client_os ||
					!\AXR\Pkgtools::test_arch_compatibility($client_arch, $file->arch))
				{
					// Skip 100% incompatible files
					continue;
				}

				$file->_version = $release->version;

				if ($client_os === 'linux')
				{
					// Compare package managers
					if ($file->type === $client_pm_ext)
					{
						return $file;
					}

					// Just in case we don't find a better match
					if ($file->type === 'tar.gz')
					{
						$close_enough = $file;
					}
				}
				else
				{
					return $file;
				}
			}

			if ($close_enough !== null)
			{
				break;
			}
		}

		return $close_enough;
	}
}
