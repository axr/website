<?php

/**
 * @deprecated
 */
class SessionPermissions
{
	/**
	 * @var \WWW\Models\User
	 */
	private $user;

	/**
	 * Constructor
	 *
	 * @param \WWW\Models\User $user
	 */
	public function __construct (\WWW\Models\User $user = null)
	{
		$this->user = $user;
	}

	/**
	 * Check is the user has the permission
	 *
	 * @param string $name
	 * @return bool
	 */
	public function has ($name)
	{
		if ($this->user === null)
		{
			return false;
		}

		return $this->user->can($name);
	}
}

