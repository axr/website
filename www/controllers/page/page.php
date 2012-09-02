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

		// if the page is a hssdoc prop, redirect
		if ($page->ctype === 'hssprop')
		{
			$this->redirect('/doc/' . $page->fields->object .
				'#' . $page->title);

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
	 * Display /doc
	 */
	public function runHssdoc ()
	{
		$this->view->sidebar = $this->renderHssdocSidebar();

		echo $this->renderView(ROOT . '/views/hssdoc.html');
	}

	/**
	 * Display HSS object pages
	 */
	public function runHssdocObj ($object)
	{
		$query = $this->dbh->prepare('SELECT `index`.*, `page`.*
			FROM `www_pages_index` AS `index`,
				`www_pages` AS `page`
			WHERE `page`.`id` = `index`.`page_id` AND
				`index`.`field` = \'object\' AND
				`index`.`value` = :value');
		$query->bindValue(':value', $object, PDO::PARAM_STR);
		$query->execute();

		$pages_raw = (array) $query->fetchAll(PDO::FETCH_OBJ);
		$pages = array();

		// @todo create a static function PageModel::fromRaw() to do this
		foreach ($pages_raw as &$page)
		{
			$model = new PageModel($this->dbh, array(
				'_raw_data' => $page
			));

			if ($model->status !== PageModel::STATUS_OK)
			{
				continue;
			}

			$pages[] = $model;
			unset($page);
		}

		// Title & breadcrumb
		$this->view->_title = $object;
		$this->breadcrumb[] = array(
			'name' => 'HSS documentation',
			'link' => '/hssdoc'
		);
		$this->breadcrumb[] = array(
			'name' => $object
		);

		$this->view->object = $object;
		$this->view->sidebar = $this->renderHssdocSidebar();
		$this->view->props_html = '';

		foreach ($pages as $page)
		{
			$this->view->props_html .= $this->renderHssdocProp($page);
		}

		echo $this->renderView(ROOT . '/views/hssdoc_obj.html');
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
		$this->view->action = '/page/add/' . $arg;
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

	/**
	 * Create the sidebar for HSS documentation pages
	 *
	 * @return string HTML
	 */
	private function renderHssdocSidebar ()
	{
		$query = $this->dbh->prepare('SELECT `index`.*, `page`.*
			FROM `www_pages_index` AS `index`,
				`www_pages` AS `page`
			WHERE `page`.`id` = `index`.`page_id` AND
				`index`.`field` = \'object\'
			ORDER BY `index`.`value` ASC');
		$query->execute();

		$props = $query->fetchAll(PDO::FETCH_OBJ);
		$objects = array();

		foreach ($props as &$prop)
		{
			// Merge the fields
			$prop->fields = (object) array_merge(
				(array) json_decode($prop->fields, true),
				(array) json_decode($prop->fields_parsed, true));

			$objectName = $prop->fields->object;

			if (!isset($objects[$objectName]))
			{
				$objects[$objectName] = array();
			}

			$prop->url = '/doc/' . $objectName . '#' . $prop->title;
			$objects[$objectName][] = $prop;
		}

		$mustache = new Mustache();
		$view = new StdClass();
		$view->objects = array();

		foreach ($objects as $objectName => $objectProps)
		{
			$view->objects[] = array(
				'object' => $objectName,
				'props' => $objectProps
			);
		}

		return $mustache->render(
			file_get_contents(ROOT . '/views/hssdoc_sidebar.html'), $view);
	}

	/**
	 * Render a property item for HSS documentation page
	 *
	 * @param PageModel $page
	 * @return string
	 */
	private function renderHssdocProp (PageModel $page)
	{
		$view = new StdClass();
		$view->page = clone $page->data;
		$view->page->fields = $view->page->fields_merged;
		$view->page->fields->values =
			$this->renderHssdocValues($view->page->fields->values);
		$view->can_edit = Session::perms()->has('/page/edit/*') ||
			Session::perms()->has('/page/edit/hssprop');

		$mustache = new Mustache();
		$template = file_get_contents(ROOT . '/views/hssdoc_prop.html');

		return $mustache->render($template, $view);
	}

	/**
	 * Render HSS documenattion values table
	 *
	 * @param string $data_raw
	 * @return string
	 */
	private function renderHssdocValues ($data_raw)
	{
		$out = array();
		$data = json_decode($data_raw);

		if (!is_object($data))
		{
			return $data_raw;
		}

		$sorted = array();

		foreach ($data as $version => $rows)
		{
			if (!is_array($rows) || count($rows) === 0)
			{
				continue;
			}

			$rows[0]->_version = $version;
			$rows[0]->_count = count($rows);

			foreach ($rows as $row)
			{
				$sorted[] = $row;
			}
		}

		$view = new StdClass();
		$view->values = $sorted;

		$mustache = new Mustache();
		$template = file_get_contents(ROOT . '/views/hssdoc_prop_values.html');

		return $mustache->render($template, $view);
	}
}

