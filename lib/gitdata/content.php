<?php

namespace GitData;

require_once(SHARED . '/lib/php-markdown/markdown.php');

class Content
{
	/**
	 * File
	 */
	protected $file;

	/**
	 * Options passed to the constructor
	 */
	protected $options;

	/**
	 * Content
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Table of contents
	 */
	protected $toc;

	/**
	 * Constructor
	 */
	public function __construct ($file, array $options = array())
	{
		$this->options = array_merge(array(
			'link_titles' => false,
			'generate_toc' => false
		), $options);

		if ($file instanceof \GitData\Git\File)
		{
			$this->file = $file;
			$this->content = $file->get_data();
		}
		else
		{
			$this->content = $file;
		}

		$this->content = $this->parse_content($this->content);

		if ($this->options['link_titles'] || $this->options['generate_toc'])
		{
			preg_match_all('/<h(?P<n>[2-4])>(?P<title>.+?)<\/h\1>/', $this->content, $matches);

			for ($i = 0, $c = count($matches['title']); $i < $c; $i++)
			{
				$matched = $matches[0][$i];
				$n = $matches['n'][$i];
				$title = $matches['title'][$i];

				$alias = strtolower($title);
				$alias = str_replace(' ', '-', $alias);
				$alias = preg_replace('/[^a-z0-9-_.]/', '', $alias);

				$replacement = "<h{$n}><a href=\"#{$alias}\" name=\"{$alias}\">{$title}</a></h{$n}>";
				$this->content = str_replace($matched, $replacement, $this->content);

				if ($this->options['generate_toc'])
				{
					$this->toc[] = (object) array(
						'title' => $title,
						'alias' => $alias,
						'depth' => $n - 2
					);
				}
			}
		}
	}

	/**
	 * __toString
	 */
	public function __toString ()
	{
		return $this->content;
	}

	/**
	 * Get table of contents
	 */
	public function get_toc ()
	{
		return $this->toc;
	}

	/**
	 * Returns the type of the content file (or of the path specified)
	 * Possible values: md|html|text
	 *
	 * @return string
	 */
	public function get_content_type ()
	{
		if ($this->file instanceof \GitData\Git\File)
		{
			// Extract the file extension
			$explode = explode('.', $this->file->path);
			$extension = end($explode);

			return in_array($extension, array('md', 'html')) ? $extension : 'text';
		}

		return 'text';
	}

	/**
	 * Returns a summary of the content, if possible
	 *
	 * @todo Always return something usable
	 * @return string
	 */
	public function get_summary ()
	{
		$explode = explode('<!--more-->', $this->file->get_data());
		$summary = $explode[0];

		if ($summary === $this->content)
		{
			return null;
		}

		return $this->parse_content($summary);
	}

	protected function parse_content ($content)
	{
		if ($this->get_content_type() === 'md')
		{
			$content = Markdown($content);
		}

		if (in_array($this->get_content_type(), array('md', 'html')) &&
			$this->file instanceof \GitData\Git\File)
		{
			$content = \GitData\Asset::replace_urls_in_html(
				dirname($this->file->path), $content);
		}

		return $content;
	}
}
