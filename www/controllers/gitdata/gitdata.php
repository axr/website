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

		$asset = \GitData\Asset::get_by_path($_GET['path']);

		if ($asset === null)
		{
			throw new \HTTPException(null, 404);
		}

		$raw_data = $asset->get_data();

		$if_modified_since = (int) array_key_or($_SERVER, 'HTTP_IF_MODIFIED_SINCE', 0);
		$etag_header = array_key_or($_SERVER, 'HTTP_IF_NONE_MATCH', false);

		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $asset->get_mtime()) . ' GMT');
		header('Etag: ' . $asset->get_md5_checksum());
		header('Cache-Control: public');
		header('Content-Length: ' . strlen($raw_data));
		header('Content-Type: ' . $asset->get_mime_type());

		if (strtotime($if_modified_since) === $asset->get_mtime() ||
			$etag_header === $asset->get_md5_checksum())
		{
			header('HTTP/1.1 304 Not Modified');
		}

		echo $raw_data;
	}
}
