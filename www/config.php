<?php

require_once(SHARED . '/lib/core/config.php');

Config::set('/www/debug', false);

Config::set('/www/irc_count_file', '/dev/shm/irc_count');

Config::set('/www/downloads/repo/browser', 'axr/browser');
Config::set('/www/downloads/repo/core', 'axr/core');

if (file_exists(dirname(__FILE__) . '/config.user.php'))
{
	require_once(dirname(__FILE__) . '/config.user.php');
}
