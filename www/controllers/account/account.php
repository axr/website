<?php

namespace WWW;

class AccountController extends Controller
{
	const ERROR_OID_LAST = 1;

	public $errors;

	public function initialize ()
	{
		$this->errors = array(
			self::ERROR_OID_LAST => 'You must have at least one OpenID identity associated with your account at all times'
		);
	}

	public function run ()
	{
		if (User::current()->is_logged() === false)
		{
			throw new \HTTPException(null, 404);
		}

		$this->breadcrumb[] = array(
			'name' => 'Account',
		);

		$user = User::current();
		$oid = UserOID::find('all', array(
			'conditions' => array('user_id = ?', $user->id)
		));

		if (isset($_POST['_via_post']))
		{
			$user->set_attributes($_POST);
			$user->save();

			if ($user->is_invalid())
			{
				$this->view->errors = $user->errors->full_messages();
			}
		}

		// Handle errors from oid_rm
		if (isset($_GET['error']) &&
			isset($this->errors[$_GET['error']]))
		{
			$this->view->errors = array($this->errors[$_GET['error']]);
		}

		$this->view->user = $user;
		$this->view->oid = $oid;

		$this->view->add_oid_url = \URL::create('/auth')->query('continue', '/account');

		echo $this->renderView(ROOT . '/views/account.html');
	}

	public function run_oid_rm_POST ()
	{
		if (!User::current()->is_logged())
		{
			throw new \HTTPException(null, 404);
		}

		if (!isset($_POST['identity']))
		{
			throw new \HTTPException(null, 400);
		}

		$count = UserOID::count(array(
			'conditions' => array('user_id = ?', User::current()->id)
		));

		if ((int) $count === 1)
		{
			$this->redirect(\Config::get('/shared/www_url')
				->copy()
				->path('/account')
				->query('error', self::ERROR_OID_LAST));

			return;
		}

		try
		{
			$oid = UserOID::find_by_identity($_POST['identity']);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			throw new \HTTPException(null, 404);
		}

		if ($oid->user_id !== User::current()->id)
		{
			throw new \HTTPException(null, 404);
		}

		$oid->delete();

		$this->redirect(\Config::get('/shared/www_url')
			->copy()
			->path('/account'));
	}
}
