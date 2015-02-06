<?php

namespace WWW;

class GitDataController extends Controller
{
	private static $mime_types = array(
		// images
		'png' => 'image/png',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif',
		'svg' => 'image/svg+xml',
		'svgz' => 'image/svg+xml',

		// archives
		'zip' => 'application/zip'
	);

	/**
	 * Handle /gitdata/asset URLs
	 */
	public function run_asset ()
	{
		if (!isset($_GET['path']))
		{
			throw new \HTTPException(null, 404);
		}

		$object = git_object_lookup_bypath(\GitData\GitData::$tree, $_GET['path'], GIT_OBJ_BLOB);

		if (!$object)
		{
			throw new \HTTPException(null, 404);
		}

		$blob = git_blob_lookup(\GitData\GitData::$repo, git_object_id($object));
		$commit = \GitData\GitData::commit();

		$mtime = git_commit_time($commit);
		$sha = git_commit_id($commit);
		$extension = array_pop(explode('.', $_GET['path']));

		if (!isset(self::$mime_types[$extension]))
		{
			throw new \HTTPException(null, 404);
		}

		$if_modified_since = (int) array_key_or($_SERVER, 'HTTP_IF_MODIFIED_SINCE', 0);
		$etag_header = array_key_or($_SERVER, 'HTTP_IF_NONE_MATCH', false);

		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
		header('Etag: ' . $sha);
		header('Cache-Control: public');
		header('Content-Length: ' . git_blob_rawsize($blob));
		header('Content-Type: ' . self::$mime_types[$extension]);

		if (strtotime($if_modified_since) === $mtime || $etag_header === $sha)
		{
			header('HTTP/1.1 304 Not Modified');
		}

		echo git_blob_rawcontent($blob);
	}
}
