<?php

namespace WWW;

require_once(SHARED . '/lib/mustache/src/mustache.php');
require_once(SHARED . '/lib/mustache_filters/markdown.php');

class PageController extends Controller
{
	/**
	 * Initialize
	 */
	public function initialize ()
	{
		\Mustache\Filter::register(new \MustacheFilters\Markdown);
	}

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

		$this->view->_title = $page->title;
		$this->view->{'g/meta'}->canonical = $page->permalink;

		// Generate the breadcrumb
		{
			if ($page->type === 'blog-post')
			{
				$this->breadcrumb[] = array(
					'name' => 'Blog',
					'link' => '/blog'
				);
			}

			$this->breadcrumb[] = array(
				'name' => $page->title
			);
		}

		$this->view->page = $page;
		$this->view->authors = array();

		for ($i = 0, $c = count($page->authors); $i < $c; $i++)
		{
			$this->view->authors[] = (object) array(
				'name' => $page->authors[$i],
				'last' => !($i + 1 < $c)
			);
		}

		// Render the comments section
		if ($page->type === 'blog-post')
		{
			$comments_view = new \StdClass();
			$comments_view->disqus = array(
				'developer' => !\Config::get()->prod,
				'shortname' => \Config::get()->disqus_shortname,
				'identifier' => $page->permalink,
				'title' => str_replace('\'', '\\\'', $page->title)
			);

			$mustache = new \Mustache\Renderer();
			$this->view->comments_html = $mustache->render(
				file_get_contents(ROOT . '/views/page__comments.html'),
				$comments_view);
		}

		echo $this->render_view(ROOT . '/views/page_' . $page->type . '.html');
	}

	/**
	 * List all pages that have type `blog-post`
	 */
	public function run_blog_list ()
	{
		$index = self::get_blog_index();

		$per_page = 25;
		$count = count($index);

		$page = (int) array_key_or($_GET, 'page', 0);
		$offset = $page * $per_page;

		$posts = array();

		for ($i = $offset, $c = $offset + $per_page; $i < $c; $i++)
		{
			if (!isset($index[$i]))
			{
				break;
			}

			$posts[] = \GitData\Models\Page::find_by_path($index[$i]->path);
		}

		// Get previous and next page numbers
		$this->view->prev = $page - 1;
		$this->view->next = $page + 1;

		// Check, if generated page numbers exist
		$this->view->has_prev = $this->view->prev >= 0;
		$this->view->has_next = $this->view->next !== (int) ceil($count / $per_page);

		$this->view->posts = $posts;

		$this->view->_title = 'Blog';
		$this->breadcrumb[] = array(
			'name' => 'Blog'
		);

		echo $this->renderView(ROOT . '/views/pages_blog-post.html');
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
