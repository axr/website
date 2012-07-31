<?php

require_once(SHARED . '/lib/core/config.php');

Config::set('/www/debug', false);

Config::set('/www/db/dsn', 'mysql:host=localhost;dbname=test');
Config::set('/www/db/user', 'root');
Config::set('/www/db/pass', '');

if (file_exists(ROOT . '/config.user.php'))
{
	require_once(ROOT . '/config.user.php');
}

