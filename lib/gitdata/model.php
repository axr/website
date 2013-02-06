<?php

namespace GitData;

abstract class Model
{
	/**
	 * Parse stuff like the page content and summary.
	 *
	 * @todo Move to \GitData\Model
	 * @param \GitData\Git\File $file
	 */
	protected static function parse_content (\GitData\Git\File $file)
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
