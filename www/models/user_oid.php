<?php

namespace WWW;

require_once(SHARED . '/lib/core/model.php');

class UserOID extends \Core\Model
{
	static $table_name = 'www_users_oid';

	static $belongs_to = array(
		array('user')
	);
}
