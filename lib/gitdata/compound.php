<?php

namespace GitData;

class Compound
{
	public $info;
	public $content;

	/**
	 * __construct
	 */
	public function __construct ($info, $content)
	{
		$this->info = $info;
		$this->content = $content;
	}

	public static function load ($paths)
	{
		if (!is_array($paths))
		{
			$paths = array($paths);
		}

		for ($i = 0, $c = count($paths); $i < $c; $i++)
		{
			$ret = null;

			if (substr($paths[$i], -3) === '.md')
			{
				$ret = self::load_compound($paths[$i]);
			}
			else if (substr($paths[$i], -5) === '.json')
			{
				$ret = self::load_oldstyle($paths[$i]);
			}

			if ($ret)
			{
				return $ret;
			}
		}
	}

	public static function load_compound ($path)
	{
		$content = \GitData\Util::read($path);

		if (!$content)
		{
			return null;
		}

		$summary = null;
		$info = null;

		if (preg_match('/(?={((?:[^{}]+|{(?1)})*)})/', $content, $match) === 1)
		{
			$json = '{' . $match[1] . '}';
			$content = substr($content, strlen($json) + 2);
			$info = json_decode($json);

			if (!is_object($info))
			{
				$info = null;
			}
		}

		if (!$info)
		{
			$info = (object) array();
		}

		$info->_git_ref = git_reference_name(\GitData\GitData::$head);
		$info->_git_commit = \GitData\GitData::commit();
		$info->_basedir = dirname($path);
		$info->_filename = basename($path);

		return new Compound($info, new Content($info, $content, $summary));
	}

	public static function load_oldstyle ($info_path)
	{
		$info = \GitData\Util::read_info($info_path);

		if (!$info)
		{
			return null;
		}

		$content_path = $info->_basedir . '/content.md';

		if (isset($info->path))
		{
			$content_path = $info->_basedir . '/' . $info->path;
		}
		else if (isset($info->description_file))
		{
			$content_path = $info->_basedir . '/' . $info->description_file;
		}

		$content = \GitData\Util::read($content_path);
		$summary = null;

		if (isset($info->summary_file))
		{
			$summary = \GitData\Util::read($info->_basedir . '/' . $info->summary_file);
		}

		if ($content)
		{
			$content = new Content($info, $content, $summary);
		}

		return new Compound($info, $content);
	}
}
