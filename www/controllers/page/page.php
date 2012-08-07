<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(ROOT . '/controllers/page/page_model.php');

class PageController extends WWWController
{
	public function initialize ()
	{
		$this->rsrc->loadBundle('css/page.css');
		$this->rsrc->loadBundle('js/page.js');
	}

	/**
	 * Display a page
	 *
	 * @param int $id
	 */
	public function runDisplay ($value)
	{
		$key = is_numeric($value) ? 'id' : 'url';

		$model = new PageModel($this->dbh, array($key => $value));

		if ($model->status !== PageModel::STATUS_OK)
		{
			throw new HTTPException(null, 404);
		}

		$page = clone $model->data;

		// Check permissions
		if ($page->published !== true &&
			!Session::perms()->has('/page/view_unpub/*') &&
			!Session::perms()->has('/page/view_unpub/' . $page->ctype))
		{
			throw new HTTPException(null, 404);
		}

		// if the page has an URL alias, use it
		if ($key === 'id' && !empty($page->url))
		{
			$url = preg_replace('/^\//', '', $page->url);
			$this->redirect('/' . $url, 301);
			return;
		}

		// if the page is a hssdoc prop, redirect
		if ($page->ctype === 'hssprop')
		{
			$objectName = $page->fields->object;
			$propName = $page->title;

			$this->redirect('/doc/' . $objectName . '#' . $propName);
			return;
		}

		// Merge the fields
		$page->fields = (object) array_merge((array) $page->fields,
			(array) $page->fields_parsed);

		// Get the content type info
		$ctype = PageModel::$ctypes->{$page->ctype};

		// Customize the breadcrumb for blog posts
		if ($page->ctype === 'bpost')
		{
			$this->view->_breadcrumb[] = array(
				'name' => 'Blog',
				'link' => '/blog'
			);
		}

		$this->view->_title = $page->title;
		$this->view->_breadcrumb[] = array(
			'name' => $page->title
		);

		// Set tabs
		$this->view->_tabs_has = true;
		$this->view->_tabs[] = array(
			'name' => 'View',
			'link' => '/page/' . $page->id,
			'current' => true
		);
		$this->view->_tabs[] = array(
			'name' => 'Edit',
			'link' => '/page/' . $page->id . '/edit'
		);

		$this->view->page = $page;
		$this->view->page->ctime_formated = date('Y/m/d', $page->ctime);

		if (isset($ctype->comments) && $ctype->comments === true)
		{
			$this->view->lf_params = json_encode($model->getLfParams());
		}

		echo $this->renderView($ctype->view);
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

		$pages = $query->fetchAll(PDO::FETCH_OBJ);

		// Check object existance
		if (!is_array($pages) || count($pages) === 0)
		{
			throw new HTTPException(null, 404);
		}

		// Process the results
		foreach ($pages as &$page)
		{
			// Merge the fields
			$page->fields = (object) array_merge(
				(array) json_decode($page->fields, true),
				(array) json_decode($page->fields_parsed, true));
		}

		// Title & breadcrumb
		$this->view->_title = $object;
		$this->view->_breadcrumb[] = array(
			'name' => 'HSS documentation',
			'link' => '/hssdoc'
		);
		$this->view->_breadcrumb[] = array(
			'name' => $object
		);

		$this->view->object = $object;
		$this->view->pages = $pages;
		$this->view->can_edit = Session::perms()->has('/page/edit/*') ||
			Session::perms()->has('/page/edit/hssprop');
		$this->view->sidebar = $this->renderHssdocSidebar();

		echo $this->renderView(ROOT . '/views/hssdoc_obj.html');
	}

	/**
	 * List all pages that have type `bpost`
	 *
	 * @todo pagination
	 */
	public function runBlogList ()
	{
		$query = $this->dbh->prepare('SELECT `page`.*
			FROM `www_pages` AS `page`
			WHERE `page`.`ctype` = \'bpost\'
			ORDER BY `page`.`ctime` DESC
			LIMIT 25');
		$query->execute();

		$pages = $query->fetchAll(PDO::FETCH_OBJ);

		if (!is_array($pages) || count($pages) === 0)
		{
			throw new HTTPException(null, 404);
		}

		foreach ($pages as &$page)
		{
			// Merge the fields
			$page->fields = (object) array_merge(
				(array) json_decode($page->fields, true),
				(array) json_decode($page->fields_parsed, true));

			if (empty($page->fields->summary))
			{
				$explode = explode('<!--more-->', $page->fields->content);
				$page->fields->summary = $explode[0];
			}

			$page->permalink = !empty($page->url) ? '/' . $page->url :
				'/page/' . $page->id;

			unset($page);
		}
	
		$this->view->pages = $pages;

		$this->view->_title = 'Blog';
		$this->view->_breadcrumb[] = array(
			'name' => 'Blog'
		);

		echo $this->renderView(ROOT . '/views/pages_bpost.html');
	}

	/**
	 * Create a new page
	 *
	 * @param string $type
	 */
	public function runAdd ($type)
	{
		$this->view->_title = 'Create a new page';
		$this->view->_breadcrumb[] = array(
			'name' => 'Create a new page'
		);

		if (!Session::perms()->has('/page/create/*') &&
			!Session::perms()->has('/page/create/' . $type))
		{
			throw new HTTPException(null, 403);
		}

		if (!isset(PageModel::$ctypes->{$type}))
		{
			throw new HTTPException(null, 404);
		}

		$model = new PageModel($this->dbh, array('ctype' => $type));

		$this->view->values = $model->data;
		$this->view->fields = $model->getCtypeFieldsForView();
		$this->view->action = '/page/add/' . $type;

		if (isset($_POST['_via_post']))
		{
			if ($model->validateData())
			{
				if ($model->saveData())
				{
					$this->redirect('/page/' . $model->data->id .
						'/edit?fresh=1');
					return;
				}
				else
				{
					throw new HTTPException('Database write error', 500);
				}
			}
			else
			{
				$this->view->errors = implode('<br />', $model->errors);
			}
		}

		echo $this->renderView(ROOT . '/views/page_add.html');
	}

	/**
	 * Select a content type for the new page
	 */
	public function runAddSelect ()
	{
		$this->view->_title = 'Create a new page';
		$this->view->_breadcrumb[] = array(
			'name' => 'Create a new page'
		);


		$allowedCount = 0;
		$this->view->types = array();

		foreach (PageModel::$ctypes as $key => $ctype)
		{
			if (!Session::perms()->has('/page/create/*') &&
				!Session::perms()->has('/page/create/' . $key))
			{
				continue;
			}

			$this->view->types[] = array(
				'key' => $key,
				'name' => $ctype->name,
				'description' => isset($ctype->description) ?
					$ctype->description : null
			);

			$allowedCount++;
		}

		if ($allowedCount === 0)
		{
			throw new HTTPException(null, 403);
		}

		echo $this->renderView(ROOT . '/views/page_add_select.html');
	}

	/**
	 * Edit a page
	 *
	 * @param int $id
	 */
	public function runEdit ($id)
	{
		$this->view->_title = 'Edit page';
		$this->view->_breadcrumb[] = array(
			'name' => 'Edit page'
		);

		$this->view->_tabs_has = true;
		$this->view->_tabs[] = array(
			'name' => 'View',
			'link' => '/page/' . $id
		);
		$this->view->_tabs[] = array(
			'name' => 'Edit',
			'link' => '/page/' . $id . '/edit',
			'current' => true
		);

		$model = new PageModel($this->dbh, array('id' => $id));

		if (!Session::perms()->has('/page/edit/*') &&
			!Session::perms()->has('/page/edit/' . $model->data->ctype))
		{
			throw new HTTPException(null, 403);
		}

		$this->view->values = $model->data;
		$this->view->fields = $model->getCtypeFieldsForView();
		$this->view->action = '/page/' . $id . '/edit';
		$this->view->delete_url = '/page/' . $id . '/rm';
		$this->view->edit_mode = true;

		if (isset($_GET['fresh']) && $_GET['fresh'] === '1')
		{
			$this->view->message = 'Page successfully created!';
		}

		if (isset($_POST['_via_post']))
		{
			if ($model->validateData())
			{
				if (!$model->saveData())
				{
					throw new HTTPException('Database write error', 500);
				}
			}
			else
			{
				$this->view->errors = implode('<br />', $model->errors);
			}
		}

		echo $this->renderView(ROOT . '/views/page_add.html');
	}

	/**
	 * Delete page
	 */
	public function runRm ($id)
	{
		$this->view->_title = 'Delete page';
		$this->view->_breadcrumb[] = array(
			'name' => 'Delete page'
		);

		$model = new PageModel($this->dbh, array('id' => $id));

		$this->view->page = $model->data;
		$this->view->action = '/page/' . $id . '/rm';
		$this->view->cancel_url = '/page/' . $id;

		if (isset($_POST['_via_post']))
		{
			$model->rm();
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
}

