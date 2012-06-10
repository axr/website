<?php

require_once(SHARED . '/lib/axr/config.php');

Config::set('/shared/rsrc_url', 'http://axr.vg/sites/default/themes/axr');
Config::set('/shared/www_url', 'http://axr.vg');
Config::set('/shared/wiki_url', 'http://axr.vg/wiki');

if (file_exists(SHARED . '/config.user.php'))
{
	require_once(SHARED . '/config.user.php');
}

