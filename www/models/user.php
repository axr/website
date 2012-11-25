<?php

namespace WWW;

require_once(SHARED . '/lib/core/model.php');

class User extends \Core\Model
{
	static $table_name = 'www_users';

	private static $current = null;

	/**
	 * Check if user has a permission
	 *
	 * @param string $key
	 * @param bool $recursive = false
	 * @return bool
	 */
	public function can ($key, $recursive = true)
	{
		if ($recursive === true)
		{
			$test = explode('/', $key);

			while (count($test) > 0)
			{
				$name = implode('/', $test);

				if ($name !== $key)
				{
					$name .= '/*';
				}

				if ($this->can($name, false) === true)
				{
					return true;
				}

				array_pop($test);
			}

			return false;
		}

		return in_array($key, explode(',', $this->permissions));
	}

	/**
	 * Give user a permission
	 *
	 * @param string $key
	 */
	public function permit ($key)
	{
		$key = preg_replace('/[^a-z0-9_\/*]/', '', $key);
		$this->permissions .= ',' . $key;
	}

	/**
	 * Get currently logged in user
	 *
	 * @return User
	 */
	public static function current ()
	{
		if (!self::is_logged())
		{
			return new User();
		}

		if (self::$current === null)
		{
			try
			{
				self::$current = User::find(\Session::get('/user/id'));
			}
			catch (\ActiveRecord\RecordNotFound $e)
			{
				return new User();
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
