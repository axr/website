<?php

class Cache
{
	protected static $memcached;

	/**
	 * Initialize the cache system. This MUST be called before any of the cache
	 * functions can be used.
	 */
	public static function initialize (array $servers)
	{
		self::$memcached = new \Memcached();
		self::$memcached->addServers($servers);

		$stats = self::$memcached->getStats();
		$connected = false;

		foreach ($stats as $key => $server)
		{
			if ($server['pid'] > 0)
			{
				$connected = true;
				break;
			}
		}

		if ($connected === false)
		{
			throw new \Core\Exceptions\MemcacheFailure();
		}
	}

	/**
	 * Write data to the cache
	 *
	 * $options:
	 * - (int) expires: Max age in seconds
	 * - (string) data_version: Version of the dataset
	 *
	 * @param string $path
	 * @param mixed $data
	 * @param mixed[] $options
	 */
	public static function set ($path, $data, array $options = array())
	{
		$expires = (int) array_key_or($options, 'expires', 3600);

		if ($expires > 0)
		{
			$expires += time();
		}

		$store_obj = (object) array(
			'data' => $data,
			'data_version' => null,
			'code_version' => \Config::get()->version
		);

		if (isset($options['data_version']))
		{
			if ($options['data_version'] === 'current')
			{
				$store_obj->data_version = \GitData\GitData::$version;
			}
			else
			{
				$store_obj->data_version = $options['data_version'];
			}
		}

		self::$memcached->set($path, serialize($store_obj), $expires);
	}

	/**
	 * Get a cache value. If the path does not exist or has expired, null
	 * is returned.
	 *
	 * @param string $path
	 * @return mixed
	 */
	public static function get ($path)
	{
		$item = self::$memcached->get($path);
		$item = unserialize($item);

		if (!is_object($item))
		{
			self::rm($path);
			return null;
		}

		// This key has been set by an older version of the site
		if (isset($item->code_version) &&
			$item->code_version !== \Config::get()->version)
		{
			self::rm($path);
			return null;
		}

		// This key is for another dataset version
		if (isset($item->data_version) &&
			$item->data_version !== \GitData\GitData::$version)
		{
			self::rm($path);
			return null;
		}

		return $item->data;
	}

	/**
	 * Remove a path from the cache
	 *
	 * @param string $path
	 */
	public static function rm ($path)
	{
		self::$memcached->delete($path);
	}
}
