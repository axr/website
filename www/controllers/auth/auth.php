<?php

require_once(SHARED . '/lib/lightopenid/openid.php');
require_once(ROOT . '/lib/www_controller.php');

class AuthController extends WWWController
{
	public function initialize ()
	{
		$this->openid = new LightOpenID(Config::get('/shared/www_url'));
	}

	public function run ($mode = null)
	{
		$errors = array(
			'OpenIDCancelError' => 'User has canceled authentication',
			'OpenIDValidateError' => 'Something went wrong on the OpenID validation process. Please try again.',
			'InvalidIdentityError' => 'The supplied OpenID URL is not a valid OpenID endpoint'
		);

		if ($mode === 'openid')
		{
			try
			{
				$this->runOpenid();
			}
			catch (ErrorException $e)
			{
				$this->redirect('/auth?error=InvalidIdentityError');
			}
		}
		elseif ($mode === 'openid_assoc')
		{
			$this->runOpenidAssoc();
		}
		elseif ($mode === 'logout')
		{
			Session::set('/user/is_auth', false);
			Session::set('/user/id', null);

			$this->redirect('/');
		}
		else
		{
			$this->view->_breadcrumb[] = array(
				'name' => 'Login with OpenID',
				'current' => true
			);

			$this->view->assoc_mode = Session::get('/user/is_auth') === true;

			if (isset($_GET['error']))
			{
				$this->view->error = isset($errors[$_GET['error']]) ?
					$errors[$_GET['error']] : 'Unknown error';
			}

			echo $this->renderView(ROOT . '/views/login.html');
		}
	}

	public function runOpenid ()
	{
		if (!$this->openid->mode)
		{
			if (!isset($_POST['identifier']))
			{
				throw new HTTPException(null, 400);
			}

			$this->openid->identity = $_POST['identifier'];
			$this->openid->required = array('contact/email');
			$this->openid->optional = array('namePerson');

			$this->redirect($this->openid->authUrl());
		}
		elseif ($this->openid->mode === 'cancel')
		{
			$this->redirect('/auth?error=OpenIDCancelError');
		}
		else
		{
			if ($this->openid->validate())
			{
				$this->openidHandle($this->openid->identity,
					$this->openid->getAttributes());
			}
			else
			{
				$this->redirect('/auth?error=OpenIDValidateError');

			}
		}
	}

	public function runOpenidAssoc ()
	{
		if (!isset($_GET['pt']))
		{
			throw new HTTPException(null, 400);
		}

		$query = $this->dbh->prepare('SELECT `oid`.*
			FROM `www_users_oid` AS `oid`
			WHERE `oid`.`pending` = :pending
			LIMIT 1');
		$query->bindValue(':pending', $_GET['pt'], PDO::PARAM_STR);
		$query->execute();

		$oid = $query->fetch(PDO::FETCH_OBJ);

		if (!is_object($oid) || $oid->pending === null)
		{
			throw new HTTPException(null, 404);
		}

		if (isset($_GET['cancel']))
		{
			$query = $this->dbh->prepare('DELETE
				FROM `www_users_oid`
				WHERE `www_users_oid`.`pending` = :pending');
			$query->bindValue(':pending', $_GET['pt'], PDO::PARAM_STR);
			$query->execute();

			$this->redirect('/');
			return;
		}

		$attrs = unserialize($oid->pending_attrs);

		if (isset($_POST['_via_post']) &&
			isset($_POST['name']))
		{
			if (!Session::get('/user/is_auth'))
			{
				$query = $this->dbh->prepare('INSERT INTO `www_users`
					(`name`, `email`)
					VALUES (:name, :email)');
				$query->bindValue(':name', htmlentities($_POST['name']),
					PDO::PARAM_STR);
				$query->bindValue(':email',
					htmlentities($attrs['contact/email']), PDO::PARAM_STR);
				$query->execute();

				$userId = $this->dbh->lastInsertId();
			}
			else
			{
				$userId = Session::get('/user/id');
			}

			$query = $this->dbh->prepare('UPDATE `www_users_oid` AS `oid`
				SET `oid`.`user_id` = :user_id,
					`oid`.`pending` = NULL,
					`oid`.`pending_attrs` = NULL
				WHERE `oid`.`pending` = :pending');
			$query->bindValue(':user_id', $userId, PDO::PARAM_INT);
			$query->bindValue(':pending', $_GET['pt'], PDO::PARAM_STR);
			$query->execute();

			Session::set('/user/is_auth', true);
			Session::set('/user/id', $user->id);

			$this->redirect('/');
		}

		$this->view->pt = htmlentities($_GET['pt']);
		$this->view->identity = $oid->identity;
		$this->view->name = isset($attrs['namePerson']) ?
			$attrs['namePerson'] : null;

		if (Session::get('/user/is_auth'))
		{
			$this->view->_breadcrumb[] = array(
				'name' => 'Associate with an OpenID'
			);

			echo $this->renderView(ROOT . '/views/openid_ask_assoc.html');
		}
		else
		{
			$this->view->_breadcrumb[] = array(
				'name' => 'Create a new account'
			);

			echo $this->renderView(ROOT . '/views/openid_ask_create.html');
		}
	}

	public function openidHandle ($identity, $attrs)
	{
		$query = $this->dbh->prepare('SELECT `oid`.*
			FROM `www_users_oid` AS `oid`
			WHERE `oid`.`identity` = :identity
			LIMIT 1');
		$query->bindValue(':identity', $identity, PDO::PARAM_STR);
		$query->execute();

		$oid = $query->fetch(PDO::FETCH_OBJ);

		if (is_object($oid) && $oid->pending === null)
		{
			$query = $this->dbh->prepare('SELECT `user`.*
				FROM `www_users` as `user`
				WHERE `user`.`id` = :user_id
				LIMIT 1');
			$query->bindValue(':user_id', $oid->user_id, PDO::PARAM_INT);
			$query->execute();

			$user = $query->fetch(PDO::FETCH_OBJ);

			Session::set('/user/is_auth', true);
			Session::set('/user/id', $user->id);

			$this->redirect('/');
		}
		else // Not associated
		{
			if (is_object($oid) && $oid->pending !== null)
			{
				$this->redirect('/auth/openid_assoc?pt=' .
					rawurlencode($oid->pending));
				return;
			}

			$pendingToken = md5(uniqid($identify));

			$query = $this->dbh->prepare('INSERT INTO `www_users_oid`
				(`identity`, `pending`, `pending_attrs`)
				VALUES (:identity, :pending, :attrs)');
			$query->bindValue(':identity', $identity, PDO::PARAM_STR);
			$query->bindValue(':pending', $pendingToken, PDO::PARAM_STR);
			$query->bindValue(':attrs', serialize($attrs), PDO::PARAM_STR);
			$query->execute();

			$this->redirect('/auth/openid_assoc?pt=' .
				rawurlencode($pendingToken));
		}
	}
}

