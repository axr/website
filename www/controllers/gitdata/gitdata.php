<?php

namespace WWW;

class GitDataController extends Controller
{
	/**
	 * Handle /gitdata/asset URLs
	 */
	public function run_asset ()
	{
		if (!isset($_GET['path']))
		{
			throw new \HTTPException(null, 404);
		}

		$file = \GitData\GitData::$repo->get_file($_GET['path']);

		if ($file === null ||
			!\GitData\Asset::is_asset($file))
		{
			throw new \HTTPException(null, 404);
		}

		$last_commit = $file->get_commit();

		// These default will be used only if the requested file doesn't belong
		// to any commit. This can only happen in the local development
		// environment.
		$sha = hash('sha1', uniqid(''));
		$mtime = time();

		if ($last_commit !== null)
		{
			$sha = $last_commit->sha;
			$mtime = $last_commit->date;
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, \GitData\GitData::$root . '/' . $file->path);

		$if_modified_since = (int) array_key_or($_SERVER, 'HTTP_IF_MODIFIED_SINCE', 0);
		$etag_header = array_key_or($_SERVER, 'HTTP_IF_NONE_MATCH', false);

		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
		header('Etag: ' . $sha);
		header('Cache-Control: public');
		header('Content-Length: ' . $file->get_size());
		header('Content-Type: ' . $mime_type);

		if (strtotime($if_modified_since) === $mtime ||
			$etag_header === $sha)
		{
			header('HTTP/1.1 304 Not Modified');
		}

		echo $file->get_data();
	}
}
