<?php

namespace WWW;

require_once(SHARED . '/lib/axr/pkgtools.php');

class HomeController extends Controller
{
	public function run ()
	{
		$view = new \Core\View(ROOT . '/views/home.html');

		$package = \GitData\Models\Package::find_by_name('axr-browser');
		$release = $package->get_release('latest');

		if ($release !== null)
		{
			$view->release = self::find_best_file($release);
		}

		// Get the data for the latest blog posts section
		{
			$all_posts = PageController::_blog_index();
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

	protected static function find_best_file (\GitData\Models\PackageRelease $release)
	{
		$best = null;
		$perfect_match = false;

		$client_os = \AXR\Pkgtools::detect_os();
		$client_arch = \AXR\Pkgtools::detect_arch();
		$client_distro = \AXR\Pkgtools::detect_linux_distro();
		$client_pm_ext = \AXR\Pkgtools::get_pm_ext();

		foreach ($release->files as $file)
		{
			if ($file->os !== $client_os ||
				$file->arch !== $client_arch)
			{
				continue;
			}

			if ($client_os === 'linux')
			{
				if ($file->type === $client_pm_ext)
				{
					$perfect_match = true;
					$best = $file;

					break;
				}

				// Just in case we don't find a better match
				if ($file->type === 'tar.gz')
				{
					$best = $file;
				}
			}
			else
			{
				$best = $file;
				break;
			}
		}

		if ($best !== null)
		{
			$os_str = \AXR\Pkgtools::os_to_human($client_os);

			if ($perfect_match === true &&
				$client_distro !== null)
			{
				$os_str = \AXR\Pkgtools::distro_to_human($client_distro);
			}

			return array(
				'os_str' => $os_str,
				'version' => $release->version,
				'filename' => $best->filename,
				'url' => $best->url
			);
		}
	}
}
