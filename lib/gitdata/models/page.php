<?php

namespace GitData\Models;

class Page extends \GitData\Model
{
	protected $public = array('content', 'summary', 'permalink', 'toc', 'is_new');
	protected $attrs = array('type', 'title', 'file', 'summary_file', 'date',
		'authors', 'author_name', 'generate_toc');

	/**
	 * Permalink of the page
	 *
	 * @var string
	 */
	public $permalink;

	/**
	 * Table of contents
	 */
	public $toc;

	/**
	 * Content of the page
	 *
	 * @var string
	 */
	public $content;

	/**
	 * Summary of the page. This property is filled only for pages of type
	 * `blog-post`.
	 *
	 * @var string
	 */
	public $summary;

	/**
	 * Is the post new. This property is set only for pages of type `blog-post`
	 *
	 * @var bool
	 */
	public $is_new;

	/**
	 * __construct
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		// Set some defaults
		$this->attrs_data = (object) array(
			'authors' => array(),
			'generate_toc' => false
		);

		parent::__construct($info_file);

		if (isset($this->attrs_data->author_name))
		{
			array_unshift($this->attrs_data->authors, $this->attrs_data->author_name);
		}

		// Set the permalink
		$this->permalink = preg_replace('/^pages/', '', dirname($info_file->path));

		// Read the content
		{
			$content_path = isset($info->file) ?
				dirname($info_file->path) . '/' . $info->file :
				dirname($info_file->path) . '/content.md';
			$content_file = \GitData\GitData::$repo->get_file($content_path);

			if ($content_file === null)
			{
				throw new \GitData\Exceptions\EntityInvalid(null);
			}

			$content = new \GitData\Content($content_file, array(
				'link_titles' => true,
				'generate_toc' => $this->attrs_data->generate_toc === true
			));

			if ($this->attrs_data->generate_toc === true)
			{
				$this->toc = $content->get_toc();
			}

			$this->content = (string) $content;
		}

		// Get the summary
		if ($this->type === 'blog-post')
		{
			if (isset($this->attrs_data->summary_file))
			{
				$summary_path = dirname($info_file->path) . '/' . $this->attrs_data->summary_file;
				$summary_file = \GitData\GitData::$repo->get_file($summary_path);

				if ($summary_file !== null)
				{
					$this->summary = (string) new \GitData\Content($summary_file);
				}
			}

			if ($this->summary === null)
			{
				$this->summary = $content->get_summary();
			}

			$this->is_new = time() - strtotime($this->attrs_data->date) < 14 * 86400;
		}

		$this->_cache_write_state();
	}

	/**
	 * Find a page by path name from the URL.
	 *
	 * @param string $path
	 * @return \GitData\Models\Page
	 */
	public static function find_by_path ($path)
	{
		$path = preg_replace('/^\//', '', $path);
		$info_file = \GitData\GitData::$repo->get_file('/pages/' . $path . '/info.json');

		if ($info_file === null)
		{
			return null;
		}

		try
		{
			return Page::new_instance($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}

	public static function get_blog_index ()
	{
		$index = \Cache::get('/www/blog_index');

		if (is_object($index))
		{
			return $index;
		}

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

		\Cache::set('/www/blog_index', $index, array(
			'data_version' => 'current'
		));

		return $index;
	}
}
