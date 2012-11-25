<?php

namespace WWW;

require_once(SHARED . '/lib/lightopenid/openid.php');
require_once(SHARED . '/lib/core/url.php');

class AuthController extends Controller
{
	public function initialize ()
	{
		$this->openid = new \LightOpenID(\Config::get('/shared/www_url'));
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
			catch (\ErrorException $e)
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
			User::current()->set_logged(false);

			if (isset($_GET['continue']))
			{
				$continue = new \URL($_GET['continue']);
				$apps = \Config::get('/shared/apps');

				foreach ($apps as $id => $app)
				{
					if (in_array($continue->host, $app->domains))
					{
						$this->redirect($continue);
						return;
					}
				}
			}

			$this->redirect('/');
		}
		elseif ($mode === 'ra_sid_frame')
		{
			if (!User::current()->is_logged())
			{
				// Nobody is logged in which means we have nothing to say
				echo '0';
				return;
			}

			$mustache = new \Mustache\Renderer();
			$template = file_get_contents(ROOT . '/views/message_frame.html');

			$apps = \Config::get('/shared/apps');

			try
			{
				if (!isset($_GET['app_id']) ||
					!isset($_GET['respond_to']) ||
					!isset($apps->{$_GET['app_id']}))
				{
					throw new \HTTPException('Bad Request', 400);
				}

				$app = $apps->{$_GET['app_id']};
				$respond_to = new \URL($_GET['respond_to']);

				if (!in_array($respond_to->host, $app->domains))
				{
					throw new \HTTPException('Bad Request', 400);
				}

				echo $mustache->render($template, array(
					'vars' => json_encode(array(
						'respond_to' => (string) $respond_to,
						'message' => json_encode(array(
							'sid' => \Session::get_sid()
						))
					))
				));
			}
			catch (\HTTPException $e)
			{
				echo $e->getCode() . ':' . $e->getMessage();
			}
		}
		else
		{
			$this->view->_breadcrumb[] = array(
				'name' => 'Login with OpenID',
				'current' => true
			);

			$this->view->assoc_mode = \Session::get('/user/is_auth') === true;
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
				throw new \HTTPException(null, 400);
			}

			$this->openid->identity = $_POST['identifier'];
			$this->openid->required = array('contact/email');
			$this->openid->optional = array('namePerson');

			if (isset($_POST['continue']) || !empty($_POST['continue']))
			{
				$continue = \URL::create($_POST['continue'])->path;

				$this->openid->returnUrl = \URL::create()
					->from_string(\Config::get('/shared/www_url'))
					->path('/auth/openid')
					->query('continue', $continue)
					->to_string();
			}

			$this->redirect_raw($this->openid->authUrl());
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
			throw new \HTTPException(null, 400);
		}

		try
		{
			$oid = UserOID::find_by_pending($_GET['pt']);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new \HTTPException(null, 404);
		}

		if ($oid->pending === null)
		{
			throw new \HTTPException(null, 404);
		}

		if (isset($_GET['cancel']))
		{
			$oid->delete();

			$this->redirect('/');
			return;
		}

		$attrs = unserialize($oid->pending_attrs);

		if (isset($_POST['_via_post']))
		{
			if (!User::is_logged())
			{
				$user = new User();

				$user->name = htmlentities(array_key_or($_POST, 'name', 'Somebody'));
				$user->email = htmlentities($attrs['contact/email']);
				$user->save();
			}
			else
			{
				$user = User::current();
			}

			$oid->user_id = $user->id;
			$oid->pending = null;
			$oid->pending_attrs = null;
			$oid->save();

			$user->set_logged();

			if (isset($_GET['continue']))
			{
				$continue = \URL::create($_GET['continue'])->path;
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

		if (\Session::get('/user/is_auth'))
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
			$oid = UserOID::find_by_identity($identity);
		}
		catch(\ActiveRecord\RecordNotFound $e)
		{
			$oid = null;
		}

		if (is_object($oid) && $oid->pending === null)
		{
			$oid->user->set_logged();

			if (isset($_GET['continue']))
			{
				$continue = \URL::create($_GET['continue'])->path;

				if (strlen(trim($continue)) === 0)
				{
					$continue = '/';
				}

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
				$this->redirect(\URL::create('/auth/openid_assoc')
					->query('pt', $oid->pending)
					->to_string());

				return;
			}

			$oid = new UserOID();

			$oid->identity = $identity;
			$oid->pending = md5(uniqid($identify));
			$oid->pending_attrs = serialize($attrs);
			$oid->save();

			$this->redirect(\URL::create('/auth/openid_assoc')
				->query('pt', $oid->pending)
				->to_string());
		}
	}
}
