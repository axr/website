<?php

namespace Wiki;

class IndexController extends Controller
{
	public function run ($path)
	{
		$html = $this->get_cached_page('/wiki/index/' . hash('sha1', $path));

		if ($html !== null)
		{
			echo $html;
			return;
		}

		$path = preg_replace('/[.]+/', '.', $path);
		$full_path = \GitData\GitData::$root . '/wiki/' . $path;

		if (!is_dir($full_path))
		{
			throw new \HTTPException(null, 404);
		}

		$items = scandir($full_path);

		$paths = array();
		$pages = array();

		foreach ($items as $item)
		{
			if ($item === '.' || $item === '..')
			{
				continue;
			}

			$page = \GitData\Models\WikiPage::find_by_path($path . '/' . $item);

			if ($page !== null)
			{
				$pages[] = $page;
			}

			if (is_dir($full_path . '/' . $item))
			{
				$subitems = scandir($full_path . '/' . $item);
				$is_category = false;

				foreach ($subitems as $subitem)
				{
					if ($subitem === '.' || $subitem === '..')
					{
						continue;
					}

					if (file_exists($full_path . '/' . $item . '/' . $subitem . '/info.json'))
					{
						$is_category = true;
						break;
					}
				}

				if ($is_category)
				{
					$permalink = '/index/' . $path . '/' . $item;
					$paths[] = array(
						'name' => $path . '/' . $item,
						'permalink' => \URL::create(\Config::get()->url->wiki)
							->path(preg_replace('/[\/]+/', '/', $permalink))
					);
				}
			}
		}

		if (count($pages) === 0)
		{
			throw new \HTTPException(null, 404);
		}

		$this->view->_title = 'Index for /' . $path;

		// Generate the breadcrumb
		if (strlen($path) === 0)
		{
			$this->breadcrumb[] = array(
				'name' => 'Index'
			);
		}
		else
		{
			$this->breadcrumb[] = array(
				'name' => 'Index',
				'link' => \URL::create(\Config::get()->url->wiki)
					->path('/index')
			);

			$this->breadcrumb[] = array(
				'name' => $path
			);
		}

		$this->view->parent_path = '/' . $path;
		$this->view->paths = $paths;
		$this->view->pages = $pages;

		echo $this->render_page(ROOT . '/views/index.html', array(
			'cache_key' => '/wiki/index/' . hash('sha1', $path)
		));
	}
}
