<?php

require_once(SHARED . '/lib/autoauth/autoauth.php');

class ApiAutoAuth extends ApiBase
{
	public function execute ()
	{
		global $wgAuth, $wgUser, $wgOut;

		// Get the parameters
		$params = $this->extractRequestParams();

		\Autoauth\Autoauth::initialize();
		$data = \Autoauth\Autoauth::get_data($params['www_sid']);

		if ($data === null)
		{
			$this->getResult()->addValue(null, $this->getModuleName(), array(
				'status' => 1,
				'payload' => null
			));

			return true;
		}

		$dbr = wfGetDB(DB_SLAVE);
		$id = null;

		foreach ($data->user->oid as $identity)
		{
			$id = $dbr->selectField(
				'user_openid',
				'uoi_user',
				array('uoi_openid' => $identity),
				__METHOD__
			);

			if (is_numeric($id))
			{
				break;
			}
		}

		if (!is_numeric($id))
		{
			$this->getResult()->addValue(null, $this->getModuleName(), array(
				'status' => 1,
				'payload' => null
			));

			return true;
		}

		$user = User::newFromId($id);

		$wgAuth->updateUser($user);

		$user->setToken();
		$user->saveSettings();
		$user->setCookies();

		$this->getResult()->addValue(null, $this->getModuleName(), array(
			'status' => 0,
			'payload' => array(
				'user' => array(
					'id' => $id,
					'name' => $user->getName()
				)
			)
		));

		return true;
	}

	public function getVersion ()
	{
		return __CLASS__ . ': 1';
	}

	// Face parameter.
	public function getAllowedParams ()
	{
		return array(
			'www_sid' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			)
		);
	}
}
