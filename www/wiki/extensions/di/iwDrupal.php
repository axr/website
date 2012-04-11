<?php
/**
 * iwDrupal.php
 *
 * @author Ragnis Armus <ragnis@myopera.com>
 * @todo Prevent manual user creation
 * @todo Prevent email from being manually changed
 */

if (!defined('MEDIAWIKI')) {
	echo 'NO!';
	exit(1);
}

// Load config
require ('iwDrupalConfig.php');

$wgExtensionCredits['other'][] = array(
	'name' => 'Drupal Integration',
	'author' => 'Ragnis Armus',
	'description' => 'Log in automatically if drupal session exists', 
	'version' => '1'
);

$wgExtensionFunctions[] = 'wfDrupalIntegration';

/**
 * Subscribes to needed hooks
 */
function wfDrupalIntegration() {
	global $wgHooks;

	$wgHooks['UserLoadFromSession'][] = 'IwDrupal::hook_UserLoadFromSession';
	$wgHooks['PersonalUrls'][] = 'IwDrupal::hook_PersonalUrls';
}

/**
 * Serves as a namespace for all extension's functions
 */
class IwDrupal {
	/**
	 * Return connection handler for Drupal database
	 *
	 * @return PDO
	 */
	public static function getDrupalDBH() {
		global $iwParameters;
		static $dbh;

		if (!($dbh instanceof PDO))
		{
			$dbh = new PDO('mysql:host='.$iwParameters['DrupalDBHost'].
					';dbname='.$iwParameters['DrupalDBName'],
				$iwParameters['DrupalDBUser'],
				$iwParameters['DrupalDBPassword']);
		}

		return $dbh;
	}

	/**
	 * Return Drupal session id or null, if not available
	 *
	 * @return string
	 */
	public static function getDrupalSID() {
		return isset($_COOKIE['drupal_sid']) ? $_COOKIE['drupal_sid'] : null;
	}

	/**
	 * Get Drupal user's UID
	 *
	 * @return int
	 */
	public static function getDrupalUID() {
		global $iwParameters;
		static $uid;

		if ($uid === null) {
			$dbh = self::getDrupalDBH();
			$sid = self::getDrupalSID();
			$prefix = $iwParameters['DrupalDBPrefix'];

			$query = $dbh->prepare('SELECT `session`.`uid`
				FROM `'.$prefix.'sessions` AS `session`
				WHERE `session`.`sid` = :sid
				LIMIT 1');

			$query->bindValue(':sid', $sid, PDO::PARAM_STR);
			$query->execute();

			$session = $query->fetch(PDO::FETCH_OBJ);
			$uid = ($session === false) ? 0 : $session->uid;
		}

		return ($uid === null) ? 0 : $uid;
	}

	public static function getDrupalUser() {
		global $iwParameters;
		static $user;

		if ($user === null) {
			$dbh = self::getDrupalDBH();
			$uid = self::getDrupalUID();
			$prefix = $iwParameters['DrupalDBPrefix'];
			
			if ($uid == 0) {
				return null;
			}

			$query = $dbh->prepare('SELECT
					`user`.`uid`, `user`.`name`, `user`.`mail`
				FROM `'.$prefix.'users` AS `user`
				WHERE `user`.`uid` = :uid
				LIMIT 1');

			$query->bindValue(':uid', $uid, PDO::PARAM_INT);
			$query->execute();

			$user = $query->fetch(PDO::FETCH_OBJ);
		}

		return ($user === false) ? null : $user;
	}

	/**
	 * Perform login
	 *
	 * @param StdClass $du Drupal user
	 * @return bool
	 */	
	public static function doLogin($du) {
		global $wgUser, $wgAuth, $wgContLang;

		wfSetupSession();

		$username = str_replace('_', ' ', $wgContLang->ucfirst($du->name));
		$username = User::isUsableName($username) ? $username : crc32($du->name);

		$user = User::newFromName($username);

		if ($user->getId() == 0) {
			$user->setName($username);
			$user->addToDatabase();

			// Update user count in site stats
			$ssUpdate = new SiteStatsUpdate(0, 0, 0, 0, 1);
			$ssUpdate->doUpdate();
		}

		if ($user->getEmail() != $du->mail) {
			$user->setEmail($du->mail);
			$user->confirmEmail();
			$user->saveSettings();
		}

		$wgAuth->updateUser($user);

		$user->setToken();
		$user->saveSettings();
		$user->setCookies();

		return true;
	}

	/**
	 * Initialize
	 */
	public static function initialize() {
		global $wgUser;

		$du = self::getDrupalUser();

		if (is_object($wgUser) && $wgUser->getID() != 0 && !is_object($du)) {
			$wgUser->doLogout();
		}
	}

	/**
	 * Callback for MediaWiki hook `UserLoadFromSession`
	 *
	 * @return bool
	 */
	public static function hook_UserLoadFromSession($user, &$result) {
		$du = self::getDrupalUser();

		if (is_object($du) && $user->getName() != $du->name) {
			self::doLogin($du);
		} else if ($user->getID() == 0 && is_object($du)) {
			self::doLogin($du);
		} else {
			$user->doLogout();
		}

		return true;
	}

	/**
	 * Callback for MediaWiki hook `PersonalUrls`
	 *
	 * @return bool
	 */
	public static function hook_PersonalUrls(&$personal_urls, &$title) {
		global $wgUser, $iwParameters;

		$here = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/wiki';

		unset($personal_urls['login']);

		if (is_object($wgUser) & $wgUser->getID() != 0) {
			$personal_urls['logout'] = array(
				'text' => wfMsg('userlogout'),
				'href' => $iwParameters['DrupalLogout'].
					'?continue_to='.rawurlencode($here)
			);
		} else {
			$personal_urls['anonlogin'] = array(
				'text' => wfMsg('userlogin'),
				'href' => $iwParameters['DrupalLogin'].
					'?continue_to='.rawurlencode($here)
			);
		}
		
		return true;
	}
}

