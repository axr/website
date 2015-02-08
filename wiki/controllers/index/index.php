<?php

namespace Wiki;

class IndexController extends Controller
{
	public function run ($path)
	{
		$view = new \Core\View(ROOT . '/views/index.html');
		$view->cache_condition('data_version', \GitData\GitData::$version);
		$view->cache_condition('path', $path);

		if (!$view->load_from_cache())
		{
			list($paths, $pages) = \GitData\Models\WikiPage::list_all($path);

			$view->parent_path = '/' . $path;
			$view->paths = $paths;
			$view->pages = $pages;
		}

		$this->breadcrumb->push('Index', '/index');

		if (strlen($path) > 0)
		{
			$this->breadcrumb->push($path, '/index' . $path);
		}

		$this->layout->title = 'Index for /' . $path;
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}
}
