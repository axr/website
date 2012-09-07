<?php

namespace WWW\Models;

class UserOID extends \ActiveRecord\Model
{
	static $table_name = 'www_users_oid';

	static $belongs_to = array(
		array('user')
	);
}

