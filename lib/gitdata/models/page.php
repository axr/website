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
	 */
	public function __construct ($compound)
	{
		$this->attrs_data = (object) array(
			'type' => 'page',
			'authors' => array()
		);

		parent::__construct($compound->info);

		if (isset($this->attrs_data->author_name))
		{
			array_unshift($this->attrs_data->authors, $this->attrs_data->author_name);
		}

		// Set the permalink
		$this->permalink = preg_replace('/^pages/', '', $compound->info->_basedir);

		if (isset($compound->info->_filename))
		{
			$this->permalink .= '/' . preg_replace('/\.json\.md$/', '', $compound->info->_filename);
		}

		if ($compound->content)
		{
			$this->content = (string) $compound->content;
			$this->toc = $compound->content->get_toc();
		}

		// Get the summary
		if ($this->type === 'blog-post')
		{
			$this->summary = $compound->content->get_summary();
			$this->is_new = time() - strtotime($this->attrs_data->date) < 14 * 86400;
		}
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
		$path = preg_replace('/\/$/', '', $path);

		$compound = \GitData\Compound::load(array(
			'pages/' . $path . '/info.json',
			'pages/' . $path . '.json.md'
		));

		if ($compound)
		{
			return new Page($compound);
		}
	}

	public static function get_blog_index ()
	{
		$index = \Cache::get('/www/blog_index');

		if (is_array($index))
		{
			return $index;
		}

		$index = array();

		foreach (self::find_all_years() as $year)
		{
			foreach (self::get_blog_index_by_year($year) as $entry)
			{
				$index[] = $entry;
			}
		}

		\Cache::set('/www/blog_index', $index);

		return $index;
	}

	private static function get_blog_index_by_year ($year)
	{
		$entry = git_tree_entry_bypath(\GitData\GitData::$tree, 'pages/blog/' . $year);

		if (!$entry || git_tree_entry_type($entry) !== GIT_OBJ_TREE)
		{
			return array();
		}

		$tree = git_tree_lookup(\GitData\GitData::$repo, git_tree_entry_id($entry));

		if (!$tree)
		{
			return array();
		}

		$index = array();

		git_tree_walk($tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$index) use ($year)
		{
			$path = 'blog/' . $year . '/' . git_tree_entry_name($entry);

			if (substr($path, -8) === '.json.md')
			{
				$path = substr($path, 0, -8);
			}

			$page = Page::find_by_path($path);

			if ($page)
			{
				$index[] = (object) array(
					'date' => strtotime($page->date),
					'path' => $page->permalink
				);
			}
		}, $index);

		usort($index, function ($a, $b)
		{
			return ($a->date < $b->date) ? 1 : -1;
		});

		return $index;
	}

	private static function find_all_years ()
	{
		$entry = git_tree_entry_bypath(\GitData\GitData::$tree, 'pages/blog');

		if (!$entry || git_tree_entry_type($entry) !== GIT_OBJ_TREE)
		{
			return array();
		}

		$tree = git_tree_lookup(\GitData\GitData::$repo, git_tree_entry_id($entry));

		if (!$tree)
		{
			return array();
		}

		$years = array();

		git_tree_walk($tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$years)
		{
			$name = git_tree_entry_name($entry);

			if (is_numeric($name) && git_tree_entry_filemode($entry) == GIT_FILEMODE_TREE)
			{
				$years[] = (int) $name;
			}
		}, $years);

		rsort($years);

		return $years;
	}
}
