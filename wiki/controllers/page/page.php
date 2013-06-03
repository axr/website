<?php

namespace Wiki;

class PageController extends Controller
{
	/**
	 * Display a wiki page
	 *
	 * @param string $path
	 */
	public function run_display ($path)
	{
		$view = new \Core\View(ROOT . '/views/page.html');
		$page = \GitData\Models\WikiPage::find_by_path($path);

		if ($page === null)
		{
			throw new \HTTPException(null, 404);
		}

		// Generate the breadcrumb
		{
			$parents = explode('/', $path);
			array_pop($parents);

			while (count($parents) > 0)
			{
				$this->breadcrumb->push($parents[count($parents) - 1],
					'/index/' . implode('/', $parents));
				array_pop($parents);
			}

			$this->breadcrumb->push($page->title, $page->permalink);
		}

		$view->page = $page;

		$this->layout->title = $page->title;
		$this->layout->meta->canonical = $page->permalink;
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}
}
