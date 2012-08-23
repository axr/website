<?php

class Cache
{
	/**
	 * @var PDO
	 */
	private static $dbh;

	public static function initialize (PDO $dbh)
	{
		self::$dbh = $dbh;
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
		$options['expires'] = isset($options['expires']) ?
			(int) $options['expires'] : 3600;

		$query = self::$dbh->prepare('INSERT INTO `www_cache`
			(`key`, `data`, `expires`)
			VALUES (:key, :data, :expires)
			ON DUPLICATE KEY UPDATE `www_cache`.`data` = :data,
				`www_cache`.`expires` = :expires');
		$query->bindValue(':key', $path, PDO::PARAM_STR);
		$query->bindValue(':data', serialize($data), PDO::PARAM_STR);
		$query->bindValue(':expires', time() + $options['expires'], PDO::PARAM_INT);
		$query->execute();
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
		$query = self::$dbh->prepare('SELECT `cache`.*
			FROM `www_cache` AS `cache`
			WHERE `cache`.`key` = :key
			LIMIT 1');
		$query->bindValue(':key', $path, PDO::PARAM_STR);
		$query->execute();

		$item = $query->fetch(PDO::FETCH_OBJ);

		if (!is_object($item))
		{
			return null;
		}

		if ($item->expires < time())
		{
			self::rm($path);
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
		$query = self::$dbh->prepare('DELETE FROM `www_cache`
			WHERE `www_cache`.`key` = :key');
		$query->bindValue(':key', $path, PDO::PARAM_STR);
		$query->execute();
	}
}

