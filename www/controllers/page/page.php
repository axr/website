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
		// Yeah... If the model could just cache it, that'd be great
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
		$index = self::get_blog_index();

		$per_page = 25;
		$page = (int) array_key_or($_GET, 'page', 0);
		$offset = $page * $per_page;

		$view = new \Core\View(ROOT . '/views/pages_blog-post.html');
		$view->cache_condition('data_version', \GitData\GitData::$version);
		$view->cache_condition('page', $page);

		if (!$view->load_from_cache())
		{
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

	public static function build_blog_index ()
	{
		$blog_root_path = \GitData\GitData::$root . '/pages/blog';

		$index = array();

		$years = scandir($blog_root_path);
		rsort($years);

		foreach ($years as $year)
		{
			if (!is_numeric($year) ||
				!is_dir($blog_root_path . '/' . $year))
			{
				continue;
			}

			$items = scandir($blog_root_path . '/' . $year);

			foreach ($items as $item)
			{
				if ($item === '.' || $item === '..')
				{
					continue;
				}

				$post = \GitData\Models\Page::find_by_path(
					'/blog/' . $year . '/' . $item);

				if ($post === null)
				{
					continue;
				}

				$index[] = (object) array(
					'date' => strtotime($post->date),
					'path' => '/blog/' . $year . '/' . $item
				);
			}
		}

		usort($index, function ($a, $b)
		{
			return ($a->date < $b->date) ? 1 : -1;
		});

		return $index;
	}

	public static function get_blog_index ()
	{
		$index = \Cache::get('/www/blog_index');

		if (!is_object($index))
		{
			$index = self::build_blog_index();
			\Cache::set('/www/blog_index', $index, array(
				'data_version' => 'current'
			));
		}

		return $index;
	}
}
