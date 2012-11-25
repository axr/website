<?php

namespace Autoauth;

require_once(SHARED . '/lib/activerecord/ActiveRecord.php');
require_once(SHARED . '/lib/core/models/session.php');
require_once(SHARED . '/www/lib/autoloader.php');

class Autoauth
{
	public static function initialize ()
	{
		static $initialized;

		if ($initialized === true)
		{
			return;
		}

		$initialized = true;

		// Load configs
		require_once(SHARED . '/config.php');
		require_once(SHARED . '/www/config.php');

		// Connect to the database
		\ActiveRecord\Config::initialize(function($cfg)
		{
			$cfg->set_default_connection('default');
			$cfg->set_connections(array(
				'default' => \Config::get('/www/db/connection')
			));
		});
	}

	public static function get_data ($sid)
	{
		try
		{
			$session = \Core\Models\Session::find($sid);
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			return null;
		}

		if (!$session->is_valid() ||
			!isset($session->data->{'/user/is_auth'}) ||
			!isset($session->data->{'/user/id'}) ||
			$session->data->{'/user/is_auth'} !== true)
		{
			return null;
		}

		try
		{
			$oid = \WWW\UserOID::find('all', array(
				'conditions' => array('user_id = ?',
					$session->data->{'/user/id'})
			));
		}
		catch (\ActiveRecord\RecordNotFound $e)
		{
			return null;
		}

		$data = (object) array(
			'user' => (object) array(
				'oid' => array()
			)
		);

		foreach ($oid as $identity)
		{
			if ($identity->pending === null)
			{
				$data->user->oid[] = $identity->identity;
			}
		}

		return $data;
	}
}
