<?php

namespace WWW;

class PageController extends Controller
{
	/**
	 * Display a page
	 *
	 * @param string $path
	 */
	public function run_display ($path)
	{
		$page = \GitData\Models\Page::find_by_path($path);

		if ($page === null)
		{
			throw new \HTTPException(null, 404);
		}

		$view = new \Core\View(ROOT . '/views/page_' . $page->type . '.html');
		$view->cache_condition('data_version', \GitData\GitData::$version);
		$view->cache_condition('path', $path);

		if (!$view->load_from_cache())
		{
			if ($page->type === 'blog-post')
			{
				$view->comments = (string)
					new DisqusCommentsView($page->permalink, $page->title);
			}

			$view->page = $page;
		}

		if ($page->type === 'blog-post')
		{
			$this->breadcrumb->push('Blog', '/blog');
		}

		$this->breadcrumb->push($page->title, $page->permalink);

		$this->layout->title = $page->title;
		$this->layout->content = $view->get_rendered();
		$this->layout->meta->canonical = $page->permalink;

		echo $this->layout->get_rendered();
	}

	/**
	 * List all pages that have type `blog-post`
	 */
	public function run_blog_list ()
	{
		$per_page = 25;
		$page = (int) array_key_or($_GET, 'page', 0);
		$offset = $page * $per_page;

		$view = new \Core\View(ROOT . '/views/pages_blog-post.html');
		$view->cache_condition('data_version', \GitData\GitData::$version);
		$view->cache_condition('page', $page);

		if (!$view->load_from_cache())
		{
			$index = \GitData\Models\Page::get_blog_index();
			$posts = array();

			for ($i = $offset, $c = $offset + $per_page; $i < $c; $i++)
			{
				if (!isset($index[$i]))
				{
					break;
				}

				$posts[] = \GitData\Models\Page::find_by_path($index[$i]->path);
			}

			$this->breadcrumb->push('Blog', '/blog');

			// Get previous and next page numbers
			$view->prev = $page - 1;
			$view->next = $page + 1;

			// Check, if generated page numbers exist
			$view->has_prev = $view->prev >= 0;
			$view->has_next = $view->next !== (int) ceil(count($index) / $per_page);

			$view->posts = $posts;
		}

		$this->layout->title = 'Blog';
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}
}
