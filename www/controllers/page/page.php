<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(ROOT . '/controllers/page/page_model.php');
require_once(ROOT . '/models/page.php');

class PageController extends WWWController
{
	/**
	 * Display a page
	 *
	 * @param int|string $id
	 * @param string $findBy (url|id)
	 */
	public function runDisplay ($value, $findBy = 'url')
	{
		if ($findBy !== 'url' && $findBy != 'id')
		{
			$findBy = is_numeric($value) ? 'id' : 'url';
		}

		if ($findBy === 'id')
		{
			$page = Page::find($value);
		}
		else
		{
			$page = Page::find_by_url($value);
		}

		if ($page === null)
		{
			throw new HTTPException(null, 404);
		}

		if (!$page->can_view())
		{
			throw new HTTPException(null, 403);
		}

		// if the page has an URL alias, use it
		if ($findBy === 'id' && !empty($page->url))
		{
			$url = preg_replace('/^\//', '', $page->url);
			$this->redirect('/' . $url, 301);

			return;
		}

		// Get the content type info
		$ctype = Page::$ctypes->{$page->ctype};

		$this->view->_title = $page->title;
		$this->breadcrumb = $page->breadcrumb();

		$this->tabs[] = array(
			'name' => 'View',
			'link' => $page->permalink,
			'current' => true
		);

		// Edit tab
		if ($page->can_edit())
		{
			$this->tabs[] = array(
				'name' => 'Edit',
				'link' => '/page/' . $page->id . '/edit'
			);
		}

		$this->view->page = clone $page;
		$this->view->page->fields = $page->fields_merged;

		if (isset($ctype->comments) && $ctype->comments === true)
		{
			$comments_view = new StdClass();
			$comments_view->page = clone $page;
			$comments_view->disqus = array(
				'developer' => Config::get('/www/debug') ? 'true' : 'false',
				'shortname' => Config::get('/www/disqus/shortname'),
				'identifier' => '/page/' . $page->id,
				'title' => str_replace('\'', '\\\'', $page->title)
			);

			$mustache = new Mustache();
			$this->view->comments_html = $mustache->render(
				file_get_contents(ROOT . '/views/page__comments.html'),
				$comments_view);
		}

		echo $this->renderView($ctype->view);
	}

	/**
	 * List all pages that have type `bpost`
	 */
	public function runBlogList ()
	{
		$per_page = 25;
		$page = (int) array_key_or($_GET, 'page', 0);
		$offset = $page * $per_page;

		// Get items for current page
		$pages = Page::all(array(
			'conditions' => array('ctype = ? AND published = 1', 'bpost'),
			'limit' => $per_page,
			'offset' => $offset
		));	

		// Get total count of items
		$count = Page::count(array(
			'conditions' => array('ctype = ? AND published = 1', 'bpost')
		));

		// Get previous and next page numbers
		$this->view->prev = $page - 1;
		$this->view->next = $page + 1;

		// Check, if generated page numbers exist
		$this->view->has_prev = $this->view->prev >= 0;
		$this->view->has_next = $this->view->next !== (int) ceil($count / $per_page);

		$this->view->pages = $pages;

		$this->view->_title = 'Blog';
		$this->breadcrumb[] = array(
			'name' => 'Blog'
		);

		echo $this->renderView(ROOT . '/views/pages_bpost.html');
	}

	/**
	 * Select a content type for the new page
	 */
	public function runAdd ()
	{
		$this->view->types = array();

		foreach (Page::$ctypes as $key => $ctype)
		{
			if (!Session::perms()->has('/page/edit/*') &&
				!Session::perms()->has('/page/edit/' . $key))
			{
				continue;
			}

			$this->view->types[] = array(
				'key' => $key,
				'name' => $ctype->name,
				'description' => isset($ctype->description) ?
					$ctype->description : null
			);
		}

		if (count($this->view->types) === 0)
		{
			throw new HTTPException(null, 403);
		}

		$this->view->_title = 'Create a new page';
		$this->breadcrumb[] = array(
			'name' => 'Create a new page'
		);

		echo $this->renderView(ROOT . '/views/page_add_select.html');
	}

	/**
	 * Edit/create a page
	 *
	 * @param string $arg
	 */
	public function runEdit ($mode, $arg)
	{
		if ($mode === 'edit')
		{
			$page = Page::find($arg);
		}
		else
		{
			$page = new Page();
			$page->ctype = $arg;
		}

		if (!isset(Page::$ctypes->{$page->ctype}))
		{
			throw new HTTPException(null, 404);
		}

		if (!$page->can_edit())
		{
			throw new HTTPException(null, 403);
		}

		if (isset($_POST['_via_post']))
		{
			$page->set_attributes($_POST);

			if ($page->save() && $mode === 'add')
			{
				$this->redirect('/page/' . $page->id . '/edit');
				return;
			}

			if ($page->is_invalid())
			{
				$this->view->errors = $page->errors->full_messages();
				$this->view->has_errors = true;
			}

		}
	
		if ($mode === 'edit')
		{
			$this->view->_title = 'Edit page';
			$this->breadcrumb[] = array(
				'name' => 'Edit page'
			);

			$this->tabs[] = array(
				'name' => 'View',
				'link' => $page->permalink
			);
			$this->tabs[] = array(
				'name' => 'Edit',
				'link' => '/page/' . $page->id . '/edit',
				'current' => true
			);
		}
		else
		{
			$this->view->_title = 'Create a new page';
			$this->breadcrumb[] = array(
				'name' => 'Create a new page'
			);
		}

		$this->view->page = $page;
		$this->view->ctype = $page->ctype;
		$this->view->fields = $page->ctype_fields_for_view();
		$this->view->edit_mode = $mode === 'edit';

		echo $this->renderView(ROOT . '/views/page_add.html');
	}

	/**
	 * Delete page
	 */
	public function runRm ($id)
	{
		$page = Page::find($id);

		if ($page === null || !$page->can_rm())
		{
			throw new HTTPException(null, 404);
		}

		$this->view->_title = 'Delete page';
		$this->breadcrumb[] = array(
			'name' => 'Delete page'
		);

		$this->view->page = clone $page;
		$this->view->action = '/page/' . $id . '/rm';
		$this->view->cancel_url = '/page/' . $id;

		if (isset($_POST['_via_post']))
		{
			$page->delete();
			$this->redirect('/');
		}

		echo $this->renderView(ROOT . '/views/page_rm.html');
	}
}

