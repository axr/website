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
	 *
	 * @param string $path
	 * @param mixed $data
	 * @param mixed[] $options
	 */
	public static function set ($path, $data, array $options = array())
	{
		$expires = (int) array_key_or($options, 'expires', 3600);
		self::$memcached->set(self::path($path), serialize($data), $expires);
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
		if ($path === null)
		{
			return null;
		}

		$data = self::$memcached->get(self::path($path));

		if (!$data)
		{
			return null;
		}

		$data = unserialize($data);

		if (!$data)
		{
			return null;
		}

		return $data;
	}

	/**
	 * Remove a path from the cache
	 *
	 * @param string $path
	 */
	public static function rm ($path)
	{
		self::$memcached->delete(self::path($path));
	}

	/**
	 * Get a version-specific path.
	 *
	 * @param string $path
	 * @return string
	 */
	private static function path ($path)
	{
		return \Config::get()->version . ':' . \GitData\GitData::$version . ':' . $path;
	}
}
