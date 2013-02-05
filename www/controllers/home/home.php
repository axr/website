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

		// Get the data for the latest blog posts section
		{
			$cache_key = '/www/blog_index?dataver=' . \GitData\GitData::$version;
			$posts = \Cache::get($cache_key);

			if (!is_object($posts))
			{
				$posts = PageController::build_blog_index();
				\Cache::set($cache_key, $posts);
			}

			$this->view->blog_posts = array();

			for ($i = 0; $i < 5; $i++)
			{
				if (!isset($posts[$i]))
				{
					break;
				}

				$post = \GitData\Models\Page::find_by_path($posts[$i]->path);

				if ($post !== null)
				{
					$this->view->blog_posts[] = array(
						'title' => $post->title,
						'date' => $post->date,
						'is_new' => time() - strtotime($post->date) < 14 * 86400
					);
				}
			}
		}

		echo $this->renderView(ROOT . '/views/home.html');
	}
}
