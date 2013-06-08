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
