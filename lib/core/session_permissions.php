<?php

/**
 * @deprecated
 */
class SessionPermissions
{
	/**
	 * @var User
	 */
	private $user;

	/**
	 * Constructor
	 *
	 * @param User $user
	 */
	public function __construct (User $user = null)
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

