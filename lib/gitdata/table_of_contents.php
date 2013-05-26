<?php

namespace GitData;

class TableOfContents
{
	/**
	 * Content file
	 *
	 * @var \GitData\File
	 */
	protected $file;

	/**
	 * Table of contents
	 */
	protected $toc;

	/**
	 * Constructor
	 */
	public function __construct (\GitData\File $file)
	{
		$this->file = $file;
		$content = $file->get_data();

		preg_match_all('/<h(?P<n>[1-3])><a[^>]+name="(?P<alias>[a-z0-9-_.]+)"[^>]*>(?P<title>.+?)</a><\/h\1>/', $data, $matches);

		for ($i = 0, $c = count($matches['title']); $i < $c; $i++)
		{
			$matched = $matches[0][$i];
			$n = $matches['n'][$i];
			$title = $matches['title'][$i];

			$alias = strtolower($title);
			$alias = str_replace(' ', '-', $alias);
			$alias = preg_replace('/[^a-z0-9-_.]/', '', $alias);

			$replacement = "<h{$n}><a href=\"#{$alias}\" name=\"{$alias}\">{$title}</a></h{$n}>";

			$data = str_replace($matched, $replacement, $data);
		}
	}
}
