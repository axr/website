<?php
// Further documentation for configuration settings may be found at:
// http://www.mediawiki.org/wiki/Manual:Configuration_settings

// Protect against web entry
if (!defined('MEDIAWIKI'))
{
	exit;
}

require_once($IP . '/../../shared/bootstrap.php');

$wgSitename = 'AXR';

$wgScriptPath = '/wiki';
$wgScriptExtension = '.php';
$wgArticlePath = $wgScriptPath . '/$1';

if (isset($_SERVER['SERVER_NAME']))
{
	// The protocol and server name to use in fully-qualified URLs
	$wgServer = 'http://' . $_SERVER['SERVER_NAME'];

	if (isset($_SERVER['SERVER_PORT']) &&
		$_SERVER['SERVER_PORT'] !== '80' &&
		is_numeric($_SERVER['SERVER_PORT']))
	{
		$wgServer .= ':' . $_SERVER['SERVER_PORT'];
	}
}

// The relative URL path to the skins directory
$wgStylePath = $wgScriptPath . '/skins';

// UPO means: this is also a user preference option
$wgEnableEmail = true;
$wgEnableUserEmail = true; // UPO

$wgEmergencyContact = 'apache@localhost:8010';
$wgPasswordSender = 'apache@localhost:8010';

$wgEnotifUserTalk = false; // UPO
$wgEnotifWatchlist = false; // UPO
$wgEmailAuthentication = true;

// MySQL table options to use during installation or update
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";

// Experimental charset support for MySQL 4.1/5.0.
$wgDBmysql5 = false;

// Shared memory settings
$wgMainCacheType = CACHE_NONE;
$wgMemCachedServers = array();

// InstantCommons allows wiki to use images from http://commons.wikimedia.org
$wgUseInstantCommons  = false;

// If you use ImageMagick (or any other shell command) on a
// Linux server, this will need to be set to the name of an
// available UTF-8 locale
$wgShellLocale = 'en_US.utf8';

// Set $wgCacheDirectory to a writable directory on the web server
// to make your wiki go slightly faster. The directory should not
// be publically accessible from the web.
//$wgCacheDirectory = $IP . '/cache';

// Default language
$wgLanguageCode = 'en';

// Set default skin
$wgDefaultSkin = 'axrbook';

// Path to the GNU diff3 utility. Used for conflict resolution.
$wgDiff3 = "/usr/bin/diff3";

// Don't use MW's JS features
$wgUseSiteJs = false;

// *
$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['edit'] = false;

// `user` group
$wgGroupPermissions['user']['edit'] = true;

// Trusted user group
$wgGroupPermissions['trusted'] = $wgGroupPermissions['user'];
$wgGroupPermissions['trusted']['autopatrol'] = true;
$wgGroupPermissions['trusted']['minoredit'] = true;

// Load passwords and stuff
if (file_exists($IP . '/LocalSettings.2.php'))
{
	include($IP . '/LocalSettings.2.php');
}

// Load Drupal intergation extension
require_once($IP . '/extensions/di/iwDrupal.php');

// Syntax highlighting
require_once($IP . '/extensions/SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.php');

// Parser functions
require_once($IP . '/extensions/ParserFunctions/ParserFunctions.php');

$wgShowExceptionDetails = true;

