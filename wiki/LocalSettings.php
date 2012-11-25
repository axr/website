<?php
// Further documentation for configuration settings may be found at:
// http://www.mediawiki.org/wiki/Manual:Configuration_settings

// Protect against web entry
if (!defined('MEDIAWIKI'))
{
	exit;
}

define('SHARED', dirname(__FILE__) . '/..');
define('ROOT', $IP);

require_once(SHARED . '/lib/core/config.php');
require_once(SHARED . '/config.php');

$wgSitename = 'AXR';

preg_match('/(https?:\/\/[^\/]+)(\/.*)?$/', Config::get('/shared/wiki_url'), $match);

$wgScriptPath = isset($match[2]) ? $match[2] : '';
$wgScriptExtension = '.php';
$wgArticlePath = $wgScriptPath . '/$1';
$wgUsePathInfo = true;

// The protocol and server name to use in fully-qualified URLs
$wgServer = $match[1];

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
$wgMainCacheType = CACHE_ACCEL;

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

// Disable page counters
$wgDisableCounters = true;

// Disable some special pages
$wgHooks['SpecialPage_initList'][] = function (&$list)
{
	unset($list['Userlogin']);
	return true;
};

// *
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

// OpenID
require_once($IP . '/extensions/OpenID/OpenID.php');
$wgHideOpenIDLoginLink = true;
$wgOpenIDAllowExistingAccountSelection = true;

// CategoryTree
require_once($IP . '/extensions/CategoryTree/CategoryTree.php');
$wgUseAjax = true; // Allow MW AJAX stuff
$wgEnableMWSuggest = true; // Search suggestions

// Other extensions
require_once($IP . '/extensions/Code/Code.php');
require_once($IP . '/extensions/MixedLogin/MixedLogin.php');
