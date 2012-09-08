<?php

require_once(SHARED . '/lib/core/session_permissions.php');

class Session
{
	/**
	 * Defines how long sessions live
	 */
	const EXPIRE = 86400;

	/**
	 * PDO instance
	 *
	 * @var PDO
	 */
	private static $dbh;

	/**
	 * Session ID
	 *
	 * @var string
	 */
	private static $sid = null;

	/**
	 * Is there a database entry for the session, or not?
	 *
	 * @var bool
	 */
	private static $isNew = true;

	/**
	 * Session data
	 *
	 * @var mixed
	 */
	private static $data = array();

	/**
	 * Initialize the session handler
	 *
	 * @param PDO
	 */
	public static function initialize (PDO $dbh)
	{
		self::$dbh = $dbh;

		if (isset($_COOKIE['axr_www_sid']))
		{
			$sid = $_COOKIE['axr_www_sid'];

			$query = self::$dbh->prepare('SELECT `session`.*
				FROM `www_sessions` AS `session`
				WHERE `session`.`id` = :sid
				LIMIT 1');
			$query->bindValue(':sid', $sid, PDO::PARAM_STR);
			$query->execute();

			$session = $query->fetch(PDO::FETCH_OBJ);

			if (is_object($session))
			{
				if ($session->expires > time())
				{
					self::$isNew = false;
					self::$sid = $session->id;
					self::$data = unserialize($session->data);
				}
				else
				{
					$query = self::$dbh->prepare('DELETE
						FROM `www_sessions`
						WHERE `www_sessions`.`id` = :sid');
					$query->bindValue(':sid', $sid, PDO::PARAM_STR);
					$query->execute();
				}
			}
		}

		if (self::$sid === null)
		{
			self::$sid = sha1(uniqid(time() . $_SERVER['REMOTE_ADDR']));

			$domain = preg_replace('/^https?:\/\/([a-z0-9-_\.]+)/i', '$1',
				Config::get('/shared/www_url'));

			setcookie('axr_www_sid', self::$sid, 0, '/', $domain, false, true);
		}
	}

	/**
	 * Set a value to the session store
	 *
	 * @param string $path
	 * @param string $value
	 */
	public static function set ($path, $value)
	{
		self::$data[$path] = $value;
	}

	/**
	 * Get a value from the session store. if the value is not found,
	 * null is returned.
	 *
	 * @param string $path
	 * @return mixed
	 */
	public static function get ($path)
	{
		if (isset(self::$data[$path]))
		{
			return self::$data[$path];
		}

		return null;
	}

	/**
	 * Save the session data to the database
	 */
	public static function save ()
	{
		if (self::$isNew)
		{
			$query = self::$dbh->prepare('INSERT INTO `www_sessions`
				(`id`, `data`, `expires`)
				VALUES (:sid, :data, :expires)');
		}
		else
		{
			$query = self::$dbh->prepare('UPDATE `www_sessions` AS `session`
				SET `session`.`data` = :data,
					`session`.`expires` = :expires
				WHERE `session`.`id` = :sid');
		}

		$query->bindValue(':sid', self::$sid, PDO::PARAM_STR);
		$query->bindValue(':data', serialize(self::$data), PDO::PARAM_STR);
		$query->bindValue(':expires', time() + self::EXPIRE, PDO::PARAM_INT);
		$query->execute();
	}

	/**
	 * Get the permissions object
	 *
	 * @deprecated
	 */
	public static function perms ()
	{
		static $perms;

		if (!($perms instanceof SessionPermissions))
		{
			$perms = new SessionPermissions(\WWW\Models\User::current());
		}

		return $perms;
	}
}

