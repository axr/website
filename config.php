<?php

require_once(SHARED . '/lib/core/config.php');
require_once(SHARED . '/lib/core/url.php');

/**
 * This specified whether the site is running in production or development
 * environment.
 */
Config::set('/shared/prod', true);

/**
 * Base URLs
 */
Config::set('/shared/rsrc_url', new \URL('http://static.axrproject.org/prod-2'));
Config::set('/shared/www_url', new \URL('http://axrproject.org'));
Config::set('/shared/hssdoc_url', new \URL('http://hss.axrproject.org'));
Config::set('/shared/wiki_url', new \URL('http://wiki.axrproject.org'));

/**
 * When in production, this should be set to the deployed commit's SHA. In
 * development environment, this is usually `0000000`
 */
Config::set('/shared/version', '0000000');

/**
 * Memcached servers info
 */
Config::set('/shared/cache_servers', array(
	array('localhost', 11211)
));

/**
 * Google Analytics account IDs
 */
Config::set('/shared/ga_accounts', (object) array(
	'default' => 'UA-20384487-1'
));

if (file_exists(dirname(__FILE__) . '/config.user.php'))
{
	require_once(dirname(__FILE__) . '/config.user.php');
}
