<?php

class SessionPermissions
{
	/**
	 * All permissions are stored here
	 *
	 * @var StdClass[]
	 */
	private $perms = array();

	/**
	 * PDO instance
	 *
	 * @var PDO
	 */
	private $dhh;

	/**
	 * ID of the user that is currently loaded
	 *
	 * @var int
	 */
	private $userId = null;

	/**
	 * Constructor
	 *
	 * @param PDO $dbh
	 */
	public function __construct (PDO $dbh)
	{
		$this->dbh = $dbh;
	}

	/**
	 * Load permissions for user $id
	 *
	 * @param int $id
	 * @return bool status
	 */
	public function loadUser ($id)
	{
		$this->userId = $id;

		$query = $this->dbh->prepare('SELECT `user`.*
			FROM `www_users` AS `user`
			WHERE `user`.`id` = :id
			LIMIT 1');
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute();

		$user = $query->fetch(PDO::FETCH_OBJ);

		if (!is_object($user))
		{
			return false;
		}

		$perms = explode(',', $user->permissions);

		foreach ($perms as $p)
		{
			$this->perms[$p] = (object) array('persistent' => true);
		}

		return true;
	}

	/**
	 * Check is the user has the permission
	 *
	 * @param string $name
	 * @return bool
	 */
	public function has ($name)
	{
		return isset($this->perms[$name]);
	}

	/**
	 * Give user a permission. The name must start with a forward slash (/)
	 *
	 * @param string $name
	 * @return bool
	 */
	public function give ($name)
	{
		if ($this->userId === null)
		{
			return false;
		}

		$name = str_replace(',', '', $name);
		$this->perms[$name] = (object) array('persistent' => true);

		return true;
	}

	/**
	 * Give user a tempoarry permission which expires when the session
	 * ends. The must start with a forward slash (/)
	 *
	 * @param string $name
	 * @return bool status
	 */
	public function giveTemporary ($name)
	{
		$name = str_replace(',', '', $name);
		$this->perms[$name] = (object) array('persistent' => false);

		return true;
	}

	/**
	 * Save the permissions to the databse
	 */
	public function save ()
	{
		$perms = array();

		foreach ($this->perms as $name => $p)
		{
			if ($p->persistent === true)
			{
				$perms[] = $name;
			}
		}

		$query = $this->dbh->prepare('UPDATE `www_users` AS `user`
			SET `user`.`permissions` = :perms
			WHERE `user`.`id` = :id');
		$query->bindValue(':perms', implode(',', $perms), PDO::PARAM_STR);
		$query->bindValue(':id', $this->userId, PDO::PARAM_INT);
		$query->execute();
	}
}

