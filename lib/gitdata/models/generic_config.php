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
	 * @param \GitData\Git\File $info_file
	 */
	public function __construct (\GitData\Git\File $info_file)
	{
		$info = json_decode($info_file->get_data());

		if (!is_object($info))
		{
			throw new \GitData\Exceptions\EntityInvalid(null);
		}

		foreach ($info as $key => $value)
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
		$path = preg_replace('/^\//', '', $path);
		$info_file = \GitData\GitData::$repo->get_file($path);

		if ($info_file === null)
		{
			return null;
		}

		try
		{
			return new GenericConfig($info_file);
		}
		catch (\GitData\Exceptions\EntityInvalid $e)
		{
			return null;
		}
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
