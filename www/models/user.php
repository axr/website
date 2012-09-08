<?php

namespace WWW\Models;

class User extends \ActiveRecord\Model
{
	static $table_name = 'www_users';

	private static $current = null;

	/**
	 * Get currently logged in user
	 *
	 * @return \WWW\Models\User
	 */
	public static function current ()
	{
		if (!self::is_logged())
		{
			return null;
		}

		if (self::$current === null)
		{
			try
			{
				self::$current = User::find(\Session::get('/user/id'));
			}
			catch (\ActiveRecord\RecordNotFound $e)
			{
				return null;
			}
		}

		return self::$current;
	}

	/**
	 * Set this user as currently authenticated user
	 */
	public function set_logged ($in = true)
	{
		if ($in === true)
		{
			self::$current = $this;

			\Session::set('/user/is_auth', true);
			\Session::set('/user/id', $this->id);

			return;
		}

		self::$current = null;

		\Session::set('/user/is_auth', false);
		\Session::set('/user/id', null);
	}

	/**
	 * Check, if there is a user logged in
	 *
	 * @return bool
	 */
	public static function is_logged ()
	{
		return \Session::get('/user/is_auth') === true;
	}
}

