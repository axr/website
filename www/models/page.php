<?php

class Page extends ActiveRecord\Model
{
	static $table_name = 'www_pages';

	static $before_save = array('parse_fields', 'encode_fields');
	static $after_update = array('decode_fields');
	static $after_construct = array('decode_fields', 'virtual_fields');

	static $validates_presence_of = array(
		array('title')
	);

	static $validates_uniqueness_of = array(
		array('url')
	);

	static $ctypes;

	/**
	 * Merged fields
	 *
	 * @var StdClass
	 */
	public $fields_merged;

	/**
	 * Permalink to the page
	 *
	 * @var string
	 */
	public $permalink;

	/**
	 * Validate data
	 */
	public function validate ()
	{
		if (!in_array($this->ctype, array_keys((array) Page::$ctypes)))
		{
			$this->errors->add('ctype', 'Invalid content type');
		}

		foreach (Page::$ctypes->{$this->ctype}->fields as $field)
		{
			if (isset($field->required) && $field->required === true &&
				empty($this->fields->{$field->key}))
			{
				$this->errors->add('field_' . $field->key, ' can\'t be empty');
			}
		}
	}

	/**
	 * Encode fields
	 */
	public function encode_fields ()
	{
		$this->fields = json_encode($this->fields);
		$this->fields_parsed = json_encode($this->fields_parsed);
	}

	/**
	 * Decode fields
	 */
	public function decode_fields ()
	{
		$this->fields = json_decode($this->fields);
		$this->fields_parsed = json_decode($this->fields_parsed);

		if (!is_object($this->fields))
		{
			$this->fields = new StdClass();
		}

		if (!is_object($this->fields_parsed))
		{
			$this->fields_parsed = new StdClass();
		}
	}

	/**
	 * Parse fields before saving
	 */
	public function parse_fields ()
	{
		foreach (Page::$ctypes->{$this->ctype}->fields as $field)
		{
			if (!isset($field->filters) || !is_array($field->filters))
			{
				continue;
			}

			$this->fields_parsed->{$field->key} = $this->fields->{$field->key};

			foreach ($field->filters as $filter)
			{
				if (method_exists($this, 'filter_' . $filter))
				{
					$this->fields_parsed->{$field->key} = call_user_func(
						array($this, 'filter_' . $filter),
						$this->fields_parsed->{$field->key});
				}
			}

			if ($this->fields_parsed->{$field->key} ===
				$this->fields->{$field->key})
			{
				unset($this->fields_parsed->{$field->key});
			}
		}

	}

	/**
	 * Create virtual fields, like permalink
	 */
	public function virtual_fields ()
	{
		$this->fields_merged = (object) array_merge(
			(array) $this->fields,
			(array) $this->fields_parsed);

		$this->permalink = !empty($this->url) ? $this->url :
			'/page/' . $this->id;
	}

	/**
	 * Read content type fields data
	 *
	 * @param mixed[] $data
	 */
	public function update_attributes ($data)
	{
		parent::update_attributes(array(
			'title' => array_key_or($data, 'title', null),
			'url' => array_key_or($data, 'url', null),
			'published' => array_key_or($data, 'published', null)
		));

		if (isset(self::$ctypes->{$this->ctype}))
		{
			foreach (self::$ctypes->{$this->ctype}->fields as $field)
			{
				$this->fields->{$field->key} =
					array_key_or($data, 'field_' . $field->key, '');
			}
		}

		$this->virtual_fields();
	}

	/**
	 * Get content type specific fields for displaying in a view
	 *
	 * @return StdClass[]
	 */
	public function ctype_fields_for_view ()
	{
		if (!isset(self::$ctypes->{$this->ctype}))
		{
			return array();
		}

		$ctype = self::$ctypes->{$this->ctype};
		$fields = array();

		foreach ($ctype->fields as $_field)
		{
			$field = clone $_field;
			$fields[] = $field;

			$postKey = 'field_' . $field->key;

			$field->{'typeIs_' . $field->type} = true;

			if (isset($this->fields->{$field->key}))
			{
				$field->_value = $this->fields->{$field->key};
			}
		}

		return $fields;
	}

	/**
	 * Get breadcrumb for the page
	 *
	 * Returned array item:
	 * - string name: Name for the page
	 * - string link: Link for the page (optional)
	 *
	 * @return mixed
	 */
	public function breadcrumb ()
	{
		$breadcrumb = array();

		$breadcrumb[] = array(
			'name' => 'Home',
			'link' => '/'
		);

		if ($this->ctype === 'bpost')
		{
			$breadcrumb[] = array(
				'name' => 'Blog',
				'link' => '/blog'
			);
		}

		$breadcrumb[] = array(
			'name' => $this->title
		);

		return $breadcrumb;
	}

	/**
	 * Check, if the user can view this page
	 *
	 * @return bool
	 */
	public function can_view ()
	{
		return $this->can_do('view_unpub');
	}

	/**
	 * Check if the user can create this page
	 *
	 * @return bool
	 */
	public function can_create ()
	{
		return $this->can_do('create');
	}

	/**
	 * Check if the user can edit this page
	 *
	 * @return bool
	 */
	public function can_edit ()
	{
		return $this->can_do('edit');
	}

	/**
	 * Check if the user can remove this page
	 *
	 * @return bool
	 */
	public function can_rm ()
	{
		return $this->can_do('rm');
	}

	/**
	 * Check if the user can do $action with this page
	 *
	 * @param string $action
	 * @return bool
	 */
	private function can_do ($action)
	{
		if ($this->published === 1 ||
			Session::perms()->has('*') ||
			Session::perms()->has('/page/*') ||
			Session::perms()->has('/page/' . $action . '/*') ||
			Session::perms()->has('/page/' . $action . '/' . $this->ctype))
		{
			return true;
		}

		return false;
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

Page::$ctypes = (object) array(
	'bpost' => (object) array(
		'name' => 'Blog post',
		'description' => 'This is used for blog posts',
		'view' => ROOT . '/views/page_page.html',
		'comments' => true,
		'fields' => array(
			(object) array(
				'key' => 'summary',
				'name' => 'Summary',
				'description' => 'This will be displayed on the blog posts listing page',
				'type' => 'textarea',
				'required' => true,
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

