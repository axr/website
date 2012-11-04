<?php

require_once(SHARED . '/lib/core/config.php');

Config::set('/www/debug', false);

Config::set('/www/db/connection', 'mysql://root:password@localhost/test');
Config::set('/www/db/dsn', 'mysql:host=localhost;dbname=test');
Config::set('/www/db/user', 'root');
Config::set('/www/db/pass', '');

Config::set('/www/irc_count_file', '/dev/shm/irc_count');

Config::set('/www/ga_account', 'UA-20384487-1');

Config::set('/www/disqus/shortname', 'axr');
Config::set('/www/downloads/releases_repo', 'axr/core');

if (file_exists(ROOT . '/config.user.php'))
{
	require_once(ROOT . '/config.user.php');
}
