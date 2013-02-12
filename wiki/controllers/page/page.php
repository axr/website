<?php

namespace Wiki;

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
	 * Display a wiki page
	 *
	 * @param string $path
	 */
	public function run_display ($path)
	{
		$html = $this->get_cached_page('/wiki/page/' . hash('sha1', $path));

		if ($html !== null)
		{
			echo $html;
			return;
		}

		$page = \GitData\Models\WikiPage::find_by_path($path);

		if ($page === null)
		{
			throw new \HTTPException(null, 404);
		}

		$this->view->_title = $page->title;
		$this->view->{'g/meta'}->canonical = $page->permalink;

		// Generate the breadcrumb
		{
			$this->breadcrumb[] = array(
				'name' => $page->title
			);
		}

		$this->tabs[] = array(
			'name' => 'History',
			'link' => $page->github_history_url
		);

		$this->view->page = $page;

		echo $this->render_view(ROOT . '/views/page.html', array(
			'cache_key' => '/wiki/page/' . hash('sha1', $path)
		));
	}
}
