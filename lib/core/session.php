<?php

require_once(SHARED . '/lib/core/models/session.php');

class Session
{
	/**
	 * Defines how long sessions live
	 */
	const EXPIRE = 86400;

	/**
	 * Session ID
	 *
	 * @var \Core\Models\Session
	 */
	private static $session = null;

	/**
	 * Initialize the session handler
	 */
	public static function initialize ()
	{
		if (isset($_COOKIE['axr_www_sid']))
		{
			$sid = $_COOKIE['axr_www_sid'];

			try
			{
				self::$session = \Core\Models\Session::find($sid);
			}
			catch (\ActiveRecord\RecordNotFound $e)
			{
			}

			if (is_object(self::$session))
			{
				if (self::$session->expires < time())
				{
					self::$session->delete();
					self::$session = null;
				}
			}
		}

		if (self::$session === null)
		{
			self::$session = new \Core\Models\Session();
			self::$session->id = sha1(uniqid(time() . $_SERVER['REMOTE_ADDR']));

			setcookie('axr_www_sid', self::$session->id, 0, '/',
				'.' . Router::get_instance()->url->host, false, true);
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
		self::$session->data->{$path} = $value;
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
		if (isset(self::$session->data->{$path}))
		{
			return self::$session->data->{$path};
		}

		return null;
	}

	/**
	 * Save the session data to the database
	 */
	public static function save ()
	{
		self::$session->expires = time() + self::EXPIRE;
		self::$session->save();
	}
}

