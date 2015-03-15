<?php

namespace GitData\Models;

class WikiPage extends \GitData\Model
{
	protected $attrs = array('title', 'content_file', 'generate_toc');
	protected $public = array('content', 'mtime', 'last_author',
		'github_history_url');

	/**
	 * Content of the page
	 *
	 * @var string
	 */
	public $content;

	/**
	 * Table of contents
	 */
	public $toc;

	/**
	 * @var int
	 */
	public $mtime;

	/**
	 * The last person that edited this file
	 *
	 * @var string
	 */
	public $last_author;

	/**
	 * URL to the GitHub history page
	 *
	 * @var string
	 */
	public $github_history_url;

	/**
	 * __construct
	 */
	public function __construct ($compound)
	{
		parent::__construct($compound->info);

		if ($compound->content)
		{
			$this->content = (string) $compound->content;
			$this->toc = $compound->content->get_toc();
		}

		$this->permalink = preg_replace('/^wiki/', '/', $compound->info->_basedir);
		$this->permalink = preg_replace('/[\/]+/', '/', $this->permalink);

		if (isset($compound->info->_filename))
		{
			$this->permalink .= preg_replace('/\.json\.md$/', '', $compound->info->_filename);
		}

		$this->github_history_url = 'https://github.com/axr/website-data';

		// Get last modified date and last author
		{
			$author = git_commit_author($compound->info->_git_commit);

			if ($author)
			{
				$this->last_author = "{$author['name']} <{$author['email']}>";
				$this->mtime = $author['time'];
			}
		}
	}

	/**
	 * Find a page by path name from the URL.
	 *
	 * @param string $path
	 * @return \GitData\Models\WikiPage
	 */
	public static function find_by_path ($path)
	{
		$path = preg_replace('/^\//', '', $path);
		$compound = \GitData\Compound::load(array(
			'wiki/' . $path . '/info.json',
			'wiki/' . $path . '.json.md'
		));

		if ($compound)
		{
			return new WikiPage($compound);
		}
	}

	/**
	 * List all pages and categories under the specified path.
	 */
	public static function list_all ($path)
	{
		$tree = git_object_lookup_bypath(\GitData\GitData::$tree, 'wiki/' . $path, GIT_OBJ_TREE);

		if (!$tree)
		{
			return array(array(), array());
		}

		$categories = array();
		$pages = array();

		git_tree_walk($tree, GIT_TREEWALK_PRE, function ($root, $entry, &$_1)
			use ($path, &$pages, &$categories)
		{
			$entry_name = git_tree_entry_name($entry);

			if ($entry_name === 'content.md')
			{
				return 1;
			}

			$item = preg_replace('/[\/]+/', '/', $path . '/' . $entry_name);
			$item = preg_replace('/(^\/|(\.json)?\.md$)/', '', $item);

			$page = WikiPage::find_by_path($item);

			if ($page)
			{
				$pages[] = $page;
			}

			if (WikiPage::tree_entry_is_category($entry))
			{
				$categories[] = (object) array(
					'name' => $item,
					'permalink' => \URL::create(\Config::get()->url->wiki)
						->path('/index/' . $item)
				);
			}
		}, $none);

		return array($categories, $pages);
	}

	/**
	 * Test, if a tree is a category. Once the oldstyle pages have been eliminated,
	 * this will get a lot simpler.
	 */
	public static function tree_entry_is_category ($entry)
	{
		$ret = false;

		if (git_tree_entry_filemode($entry) != GIT_FILEMODE_TREE)
		{
			return false;
		}

		$tree = git_tree_lookup(\GitData\GitData::$repo, git_tree_entry_id($entry));

		if (!$tree)
		{
			return false;
		}

		git_tree_walk($tree, GIT_TREEWALK_PRE, function ($_1, $entry, &$ret)
		{
			$filemode = git_tree_entry_filemode($entry);
			$name = git_tree_entry_name($entry);

			if ($filemode == GIT_FILEMODE_BLOB && substr($name, -8) == '.json.md')
			{
				$ret = true;
				return -1;
			}

			if ($filemode == GIT_FILEMODE_TREE)
			{
				$subtree = git_tree_lookup(\GitData\GitData::$repo, git_tree_entry_id($entry));

				if ($subtree && git_tree_entry_byname($subtree, 'info.json'))
				{
					$ret = true;
					return -1;
				}
			}
		}, $ret);

		return $ret;
	}
}
