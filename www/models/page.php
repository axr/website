<?php

class Page extends ActiveRecord\Model
{
	static $table_name = 'www_pages';

	static $before_update = array('encode_fields');
	static $after_update = array('decode_fields');
	static $after_construct = array('decode_fields');

	static $ctypes;

	/**
	 * Merged fields
	 *
	 * @var StdClass
	 */
	public $fields_merged = null;

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

		$this->fields_merged = (object) array_merge(
			(array) $this->fields,
			(array) $this->fields_parsed);
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
	),
	'hssprop' => (object) array(
		'name' => 'HSS property',
		'view' => ROOT . '/views/page_hssprop.html',
		'filterBeforeSave' => function ($data)
		{
			$objectSafe = preg_replace('/[^a-z0-9-_]/i',
				'', $data->fields->object);
			$propSafe = preg_replace('/[^a-z0-9-_]/i',
				'', $data->title);

			$data->url = 'doc/' . $objectSafe . '/' . $propSafe;
		},
		'fields' => array(
			(object) array(
				'key' => 'description',
				'name' => 'Description',
				'type' => 'textarea',
				'required' => false,
				'filters' => array('markdown')
			),
			(object) array(
				'key' => 'values',
				'name' => 'Values',
				'type' => 'textarea',
				'required' => true,
				'value' => <<<VALUE
{
	"0.x": [
		{"value": "", "default": true},
		{"value": "", since: "0"}
	]
}
VALUE
			),
			(object) array(
				'key' => 'object',
				'name' => 'Object name:',
				'type' => 'text',
				'required' => true,
				'index' => true
			)
		),
		'hide_url' => true
	)
);

