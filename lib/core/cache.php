<?php

require_once(SHARED . '/lib/core/models/cache.php');

class Cache
{
	public static function initialize ()
	{
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
	public static function set ($path, $data, $options = array())
	{
		$item = new \Core\Models\Cache();

		$item->key = $path;
		$item->data = serialize($data);
		$item->expires = time() + (int) array_key_or($options, 'expires', 3600);
		$item->save();
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
		try
		{
			$item = \Core\Models\Cache::find_by_key($path);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			return null;
		}

		if (!is_object($item))
		{
			return null;
		}

		if ($item->expires < time())
		{
			$item->delete();

			return null;
		}

		return unserialize($item->data);
	}

	/**
	 * Remove a path from the cache
	 *
	 * @param string $path
	 */
	public static function rm ($path)
	{
		try
		{
			$item = \Core\Models\Cache::find_by_key($path);
			$item->delete();
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
		}
	}
}

