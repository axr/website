<?php

namespace GitData\Models;

class GenericConfig extends \GitData\Model
{
	protected static $_instances = array();

	/**
	 * Google Analytics account ID
	 *
	 * @var string
	 */
	public $ga_account;

	/**
	 * Disqus shortname
	 *
	 * @var string
	 */
	public $disqus_shortname;

	/**
	 * Social networking profiles
	 *
	 * @var string[]
	 */
	public $social;

	/**
	 * __construct
	 *
	 * @param \StdClass $data the config
	 */
	public function __construct ($data)
	{
		foreach ($data as $key => $value)
		{
			if (property_exists(__CLASS__, $key))
			{
				$this->$key = $value;
			}
		}

		// Default values
		$this->social = (object) array_merge(array(
			'facebook' => null,
			'github' => null,
			'google_plus' => null,
			'launchpad' => null,
			'ohloh' => null,
			'twitter' => null,
			'vimeo' => null,
			'youtube' => null
		), (array) $this->social);
	}

	/**
	 * Find a config file by path
	 *
	 * @param string $path
	 * @return \GitData\Models\GenericConfig
	 */
	public static function find_by_path ($path)
	{
		$object = git_object_lookup_bypath(\GitData\GitData::$tree, $path, GIT_OBJ_BLOB);

		if (!$object)
		{
			return null;
		}

		$blob = git_blob_lookup(\GitData\GitData::$repo, git_object_id($object));
		$data = json_decode(git_blob_rawcontent($blob));

		if (!is_object($data))
		{
			return null;
		}

		return new GenericConfig($data);
	}

	/**
	 * Open a config file by path. When you open the same file multiple times
	 * using this method, you'll be given the same GenericConfig instance
	 * every time.
	 *
	 * @param string $path
	 * @return \GitData\Models\GenericConfig
	 */
	public static function file ($path)
	{
		if (!isset(self::$_instances[$path]))
		{
			$file = self::find_by_path($path);

			if ($file === null)
			{
				return null;
			}

			self::$_instances[$path] = $file;
		}

		return self::$_instances[$path];
	}
}
