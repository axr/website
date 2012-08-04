<?php

require_once(SHARED . '/lib/php-markdown/markdown.php');

class PageModel
{
	/**
	 * Content types information
	 */
	public static $ctypes;

	/**
	 * Current content type
	 */
	public $ctype;

	/**
	 * Model data
	 */
	public $data;

	/**
	 * Validation errors
	 *
	 * @var string[]
	 */
	public $errors = array();

	/**
	 * @var PDO
	 */
	private $dbh;

	/**
	 * Constructor
	 */
	public function __construct (PDO $dbh, $params = array())
	{
		$this->data = (object) array(
			'fields' => (object) array()
		);

		$this->dbh = $dbh;

		if (isset($params['ctype']) &&
			isset(self::$ctypes->{$params['ctype']}))
		{
			$this->data->ctype = $params['ctype'];
			$this->ctype = self::$ctypes->{$params['ctype']};
		}

		// Create $this->data structure
		$this->wipeData();
	
		if (isset($params['id']))
		{
			$this->data->id = $params['id'];
			$this->loadPageData();
		}
		elseif (isset($params['url']))
		{
			$this->data->url = $params['url'];
			$this->loadPageData();
		}

		$this->loadPostData();
		$this->filterData();
	}

	/**
	 * Wipe $this->data and populate it with empty keys.
	 * The `ctype` key will not be wiped since the page must have a content
	 * type at all times.
	 */
	public function wipeData ()
	{
		$this->data = (object) array(
			'id' => null,
			'title' => null,
			'url' => null,
			'published' => null,
			'ctype' => isset($this->data->ctype) ? $this->data->ctype : null,
			'fields' => new StdClass(),
			'fields_parsed' => new StdClass()
		);

		if (is_object($this->ctype))
		{
			foreach ($this->ctype->fields as $field)
			{
				$this->data->fields->{$field->key} = null;
			}
		}
	}

	/**
	 * Load page data from the database
	 *
	 * @return bool status
	 */
	public function loadPageData ()
	{
		if ($this->data->id === null &&
			$this->data->url === null)
		{
			return false;
		}

		$key = ($this->data->id === null) ? 'url' : 'id';

		$query = $this->dbh->prepare('SELECT `page`.*
			FROM `www_pages` AS `page`
			WHERE `page`.`' . $key . '` = :value
			LIMIT 1');
		$query->bindValue(':value', $this->data->{$key},
			($key === 'url') ? PDO::PARAM_STR : PDO::PARAM_INT);
		$query->execute();

		$page = $query->fetch(PDO::FETCH_OBJ);

		if (!is_object($page) ||
			!isset(self::$ctypes->{$page->ctype}))
		{
			return false;
		}

		$page->fields = json_decode($page->fields);
		$this->data = $page;
		$this->ctype = self::$ctypes->{$page->ctype};

		return true;
	}

	/**
	 * Load data from $_POST
	 */
	public function loadPostData ()
	{
		$names = array('title', 'url', 'published');

		foreach ($this->ctype->fields as $field)
		{
			$names[] = 'field_' . $field->key;
		}

		foreach ($names as $name)
		{
			if (!isset($_POST[$name]))
			{
				continue;
			}

			$insertInto = $this->data;
			$realName = $name;

			if (preg_match('/^field_(.+)$/', $name, $match))
			{
				$insertInto = $this->data->fields;
				$realName = $match[1];
			}

			$insertInto->{$realName} = $_POST[$name];
		}
	}

	/**
	 * This should be run after $this->loadPostData() and after
	 * $this->loadPageData()
	 */
	public function filterData ()
	{
		$this->data->url = preg_replace('/[^a-z0-9-+_.\/]/i',
			'', $this->data->url);
		$this->data->url = preg_replace('/^\//', '', $this->data->url);
		$this->data->published = (bool) $this->data->published;

		$this->parseFields();
	}

	/**
	 * Save the data to the database
	 *
	 * @return bool
	 */
	public function saveData ()
	{
		if ($this->data->id === null)
		{
			$query = $this->dbh->prepare('INSERT INTO `www_pages`
				(`title`, `url`, `fields`, `fields_parsed`, `ctime`,
					`mtime`, `ctype`, `published`)
				VALUES (:title, :url, :fields, :fields_p, :ctime,
					:mtime, :ctype, :published)');

			$query->bindValue(':ctime', time(), PDO::PARAM_INT);
		}
		else
		{
			$query = $this->dbh->prepare('UPDATE `www_pages` AS `page`
				SET `page`.`title` = :title,
					`page`.`url` = :url,
					`page`.`fields` = :fields,
					`page`.`fields_parsed` = :fields_p,
					`page`.`mtime` = :mtime,
					`page`.`ctype` = :ctype,
					`page`.`published` = :published
				WHERE `page`.`id` = :id');

			$query->bindValue(':id', $this->data->id, PDO::PARAM_INT);
		}

		$query->bindValue(':title', $this->data->title, PDO::PARAM_STR);
		$query->bindValue(':url', $this->data->url, PDO::PARAM_STR);
		$query->bindValue(':published', (int) $this->data->published, PDO::PARAM_INT);

		$query->bindValue(':mtime', time(), PDO::PARAM_INT);
		$query->bindValue(':ctype', $this->data->ctype, PDO::PARAM_STR);

		$query->bindValue(':fields', json_encode($this->data->fields),
			PDO::PARAM_STR);
		$query->bindValue(':fields_p', json_encode($this->data->fields_parsed),
			PDO::PARAM_STR);

		$status = $query->execute();

		if ($status && $this->data->id === null)
		{
			$this->data->id = $this->dbh->lastInsertId();
		}

		return (bool) $status;
	}

	/**
	 * Delete page permanently
	 */
	public function rm ()
	{
		if ($this->data->id !== null)
		{
			$query = $this->dbh->prepare('DELETE FROM `www_pages`
				WHERE `www_pages`.`id` = :id');
			$query->bindValue(':id', $this->data->id, PDO::PARAM_INT);
			$query->execute();
		}

		$this->data = null;
		$this->errors = null;
	}

	/**
	 * Validate data in $this->data
	 *
	 * @return bool
	 */
	public function validateData ()
	{
		if (strlen(trim($this->data->title)) === 0)
		{
			$this->errors[] = 'Title cannot be empty';
		}

		if ($this->data->id === null &&
			$this->checkIfUrlExists($this->data->url))
		{
			$this->errors[] = 'This URL is already in use';
		}

		foreach ($this->ctype->fields as $field)
		{
			if (!isset($this->data->fields->{$field->key}))
			{
				$this->errors[] = 'Field <em>' . $field->key . '</em> missing';
				continue;
			}

			if ($field->required === true &&
				strlen(trim($this->data->fields->{$field->key})) === 0)
			{
				$this->errors[] = 'Field <em>' . $field->name .
					'</em> must not be empty';
				continue;
			}
		}

		return count($this->errors) === 0;
	}	

	/**
	 * @return StdClass[]
	 */
	public function getCtypeFieldsForView ()
	{
		$fields = array();

		foreach ($this->ctype->fields as $_field)
		{
			$field = clone $_field;
			$fields[] = $field;

			$postKey = 'field_' . $field->key;

			$field->{'typeIs_' . $field->type} = true;
			$field->_value = $this->data->fields->{$field->key};
		}

		return $fields;
	}
	
	/**
	 * Get LiveFyre initialization params
	 */
	public function getLfParams ()
	{
		$params = array(
			'domain' => Config::get('/www/lf/domain'),
			'site_id' => Config::get('/www/lf/site_id'),
			'article_id' => $this->data->id
		);

		$permalink = Config::get('/shared/www_url') .
			(!empty($this->data->url) ? '/' . $this->data->url :
				'/page/' . $this->data->id);

		if (Config::get('/www/lf/secret') !== null)
		{
			$lf_params['conv_meta'] = array(
				'article_url' => $permalink,
				'title' => $page->title,
				'sig' => md5(implode(',', array(
					$page->id,
					$permalink,
					$page->title,
					Config::get('/www/lf/secret')
				)))
			);
		}

		return $params;
	}

	/**
	 * Filter content type specific fields
	 */
	public function parseFields ()
	{
		$fdata = $this->data->fields_parsed = new StdClass();

		foreach ($this->ctype->fields as $field)
		{
			if (!isset($field->filters) || !is_array($field->filters))
			{
				continue;
			}

			$fdata->{$field->key} = $this->data->fields->{$field->key};

			foreach ($field->filters as $filter)
			{
				$fn = 'filter_' . $filter;

				if (method_exists($this, $fn))
				{
					$fdata->{$field->key} = call_user_func(
						array($this, $fn), $fdata->{$field->key});
				}
			}

			if ($fdata->{$field->key} === $this->data->fields->{$field->key})
			{
				unset($fdata->{$field->key});
			}
		}
	}

	/**
	 * Check if URL exists
	 *
	 * @param string $url
	 * @retrun bool
	 */
	private function checkIfUrlExists ($url)
	{
		$query = $this->dbh->prepare('SELECT COUNT(*)
			FROM `www_pages` AS `page`
			WHERE `page`.`url` = :url
			LIMIT 1');
		$query->bindValue(':url', $url, PDO::PARAM_STR);
		$query->execute();

		return $query->fetch(PDO::FETCH_OBJ)->{'COUNT(*)'} > 0;
	}

	/**
	 * Markdown filter
	 *
	 * @param string $data
	 * @return string
	 */
	private function filter_markdown ($data)
	{
		return Markdown($data);
	}
}

PageModel::$ctypes = (object) array(
	'bpost' => (object) array(
		'name' => 'Blog post',
		'description' => 'This is used for blog posts',
		'view' => ROOT . '/views/page_bpost.html',
		'comments' => true,
		'fields' => array(
			(object) array(
				'key' => 'summary',
				'name' => 'Summary',
				'description' => 'This will be displayed on the blog posts listing page',
				'type' => 'textarea',
				'required' => false,
				'filters' => array('markdown')
			),
			(object) array(
				'key' => 'content',
				'name' => 'Content',
				'type' => 'textarea',
				'required' => true,
				'filters' => array('markdown')
			)
		)
	),
	'page' => (object) array(
		'name' => 'Basic page',
		'description' => 'Basic content type for pages like "About"',
		'view' => ROOT . '/views/page_page.html',
		'fields' => array(
			(object) array(
				'key' => 'content',
				'name' => 'Content',
				'type' => 'textarea',
				'required' => true,
				'filters' => array('markdown')
			)
		)
	)
);

