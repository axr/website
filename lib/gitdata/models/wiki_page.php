<?php

namespace GitData\Models;

class WikiPage extends \GitData\Model
{
	protected $attrs = array('title', 'content_file', 'generate_toc');
	protected $public = array('content', 'mtime_str', 'last_author',
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
	 * @var string
	 */
	public $mtime_str = '0000-00-00 00:00';

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
	 *
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		// Set some defaults
		$this->attrs_data = (object) array(
			'generate_toc' => false
		);

		parent::__construct($info_file);

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

		$this->permalink = preg_replace('/^wiki/', '', dirname($info_file->path));
		$this->github_history_url = 'https://github.com/axr/website-data/commits/master/' .
			$content_file->path;

		// Get last modified date and last author
		{
			$commit = $content_file->get_commit();

			if ($commit !== null)
			{
				$this->mtime_str = date('Y-m-d H:i', $commit->date);
				$this->last_author = $commit->author;
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
		$file = \GitData\GitData::$repo->get_file('wiki/' . $path . '/info.json');

		if ($file === null)
		{
			return null;
		}

		try
		{
			return new WikiPage($file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
	}
}
