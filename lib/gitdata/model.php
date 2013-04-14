<?php

namespace GitData;

abstract class Model
{
	/**
	 * Parse stuff like the page content and summary.
	 *
	 * $options:
	 * - (bool) link_titles: Whether to make all h1-h3 titles links. Default is
	 *   false.
	 *
	 * @todo Move to \GitData\Model
	 * @param \GitData\Git\File $file
	 * @param array $options
	 */
	protected static function parse_content (\GitData\Git\File $file, array $options = array())
	{
		$data = $file->get_data();

		if (self::get_content_type($file->path) === 'md')
		{
			$data = Markdown($data);
		}

		if (in_array(self::get_content_type($file->path), array('md', 'html')))
		{
			$data = \GitData\Asset::replace_urls_in_html(dirname($file->path), $data);
		}

		if (isset($options['link_titles']) && $options['link_titles'] === true)
		{
			preg_match_all('/<h(?P<n>[1-3])>(?P<title>.+?)<\/h\1>/', $data, $matches);

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

		return $data;
	}

	/**
	 * Returns the type of the content file (or of the path specified)
	 * Possible values: md|html|text
	 *
	 * @todo Move to \GitData\Model
	 * @param string $path
	 * @return string
	 */
	protected static function get_content_type ($path)
	{
		// Extract the file extension
		$explode = explode('.', $path);
		$extension = end($explode);

		return in_array($extension, array('md', 'html')) ? $extension : 'text';
	}
}
