<?php

namespace GitData;

class Util
{
	/**
	 * Read a file by path.
	 */
	public static function read ($path)
	{
		$object = git_object_lookup_bypath(\GitData\GitData::$tree, $path, GIT_OBJ_BLOB);

		if (!$object)
		{
			return null;
		}

		$blob = git_blob_lookup(\GitData\GitData::$repo, git_object_id($object));

		return git_blob_rawcontent($blob);
	}

	/**
	 * Attempt to read and parse an info file.
	 */
	public static function read_info ($path)
	{
		$object = git_object_lookup_bypath(\GitData\GitData::$tree, $path, GIT_OBJ_BLOB);

		if (!$object)
		{
			return null;
		}

		$blob = git_blob_lookup(\GitData\GitData::$repo, git_object_id($object));
		$info = self::read_info_from_blob($blob);

		if ($info)
		{
			$info->_basedir = dirname($path);
		}

		return $info;
	}

	public static function read_info_from_blob ($blob)
	{
		if (!$blob)
		{
			return null;
		}

		$info = json_decode(git_blob_rawcontent($blob));

		if (!is_object($info))
		{
			return null;
		}

		$info->_git_ref = git_reference_name(\GitData\GitData::$head);
		$info->_git_commit = \GitData\GitData::commit();

		return $info;
	}
}
