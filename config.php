<?php

require_once(SHARED . '/lib/core/config.php');
require_once(SHARED . '/lib/core/url.php');

Config::set('/shared/rsrc/prod', true);

Config::set('/shared/rsrc_url', new \URL('http://static.axrproject.org/prod-2'));
Config::set('/shared/www_url', new \URL('http://axrproject.org'));
Config::set('/shared/hssdoc_url', new \URL('http://hss.axrproject.org'));
Config::set('/shared/wiki_url', new \URL('http://wiki.axrproject.org'));

// When in production, this should be set to the deployet commit's SHA
Config::set('/shared/version', '0000000');

Config::set('/shared/cache_servers', array(
	array('localhost', 11211)
));

Config::set('/shared/apps', (object) array(
	'www' => (object) array(
		'domains' => array('axrproject.org', 'hss.axrproject.org')
	),
	'wiki' => (object) array(
		'domains' => array('wiki.axrproject.org')
	)
));

/**
 * Reccommended development values. You can put them in config.user.php
 *

Config::set('/shared/rsrc/prod', false);

$localhost = 'http://localhost';
Config::set('/shared/rsrc_url', $localhost . '/static);
Config::set('/shared/www_url', $localhost;
Config::set('/shared/wiki_url', $localhost . '/wiki');

 */

if (file_exists(dirname(__FILE__) . '/config.user.php'))
{
	require_once(dirname(__FILE__) . '/config.user.php');
}
