<?php

require_once(SHARED . '/lib/lightopenid/openid.php');
require_once(ROOT . '/lib/www_controller.php');
require_once(ROOT . '/models/user.php');
require_once(ROOT . '/models/user_oid.php');

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

			if (isset($_GET['continue']))
			{
				$continue = Router::parseUrl($_GET['continue'])->path;
				$this->redirect($continue);
			}
			else
			{
				$this->redirect('/');
			}
		}
		else
		{
			$this->view->_breadcrumb[] = array(
				'name' => 'Login with OpenID',
				'current' => true
			);

			$this->view->assoc_mode = Session::get('/user/is_auth') === true;
			$this->view->continue = array_key_or($_GET, 'continue', '');

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

			if (isset($_POST['continue']) || !empty($_POST['continue']))
			{
				$continue = Router::parseUrl($_POST['continue'])->path;

				$this->openid->returnUrl = Router::buildUrl(
					Config::get('/shared/www_url'),
					array(
						'path' => '/auth/openid',
						'query' => array(
							'continue' => $continue
						)
					)
				);
			}

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

		try
		{
			$oid = \WWW\Models\UserOID::find_by_pending($_GET['pt']);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new HTTPException(null, 404);
		}

		if ($oid->pending === null)
		{
			throw new HTTPException(null, 404);
		}

		if (isset($_GET['cancel']))
		{
			$oid->delete();

			$this->redirect('/');
			return;
		}

		$attrs = unserialize($oid->pending_attrs);

		if (isset($_POST['_via_post']) &&
			isset($_POST['name']))
		{
			if (!Session::get('/user/is_auth'))
			{
				$user = new \WWW\Models\User();

				$user->name = htmlentities($_POST['name']);
				$user->email = htmlentities($attrs['contact/email']);
				$user->save();

				$user_id = $user->id;
			}
			else
			{
				$user_id = Session::get('/user/id');
			}

			$oid->user_id = $user_id;
			$oid->pending = null;
			$oid->pending_attrs = null;
			$oid->save();

			Session::set('/user/is_auth', true);
			Session::set('/user/id', $user_id);
			
			if (isset($_GET['continue']))
			{
				$continue = Router::parseUrl($_GET['continue'])->path;
				$this->redirect($continue);
			}
			else
			{
				$this->redirect('/');
			}
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
		try
		{
			$oid = \WWW\Models\UserOID::find_by_identity($identity);
		}
		catch(\ActiveRecord\RecordNotFound $e)
		{
			$oid = null;
		}

		if (is_object($oid) && $oid->pending === null)
		{
			Session::set('/user/is_auth', true);
			Session::set('/user/id', $oid->user->id);

			if (isset($_GET['continue']))
			{
				$continue = Router::parseUrl($_GET['continue'])->path;
				$this->redirect($continue);
			}
			else
			{
				$this->redirect('/');
			}
		}
		else // Not associated
		{
			if (is_object($oid) && $oid->pending !== null)
			{
				// TODO: Use the router to build this URL
				$this->redirect('/auth/openid_assoc?pt=' .
					rawurlencode($oid->pending));
				return;
			}

			$oid = new \WWW\Models\UserOID();

			$oid->identity = $identity;
			$oid->pending = md5(uniqid($identify));
			$oid->pending_attrs = serialize($attrs);
			$oid->save();

			// TODO: Use the router to build this URL
			$this->redirect('/auth/openid_assoc?pt=' .
				rawurlencode($oid->pending));
		}
	}
}

