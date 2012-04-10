<?php

if (!defined('MEDIAWIKI')) {
	echo 'NO!';
	exit(1);
}

/**
 * If key `$key` exists in array `$array`, return it's value,
 * otherwise return `$value`.
 *
 * @param mixed $array
 * @param string $key
 * @param mixed $value
 * @return mixed
 */
function di_tot($array, $key, $value) {
	return array_key_exists($key, $array) ? $array[$key] : $value;
}

$iwParameters = isset($iwParameters) ? $iwParameters : array();
$iwParameters['DrupalLogin'] = di_tot($iwParameters, 'DrupalLogin', '/user/login');
$iwParameters['DrupalLogout'] = di_tot($iwParameters, 'DrupalLogout', '/user/logout');
$iwParameters['DrupalDBHost'] = di_tot($iwParameters, 'DrupalDBHost', 'localhost');
$iwParameters['DrupalDBUser'] = di_tot($iwParameters, 'DrupalDBUser', 'root');
$iwParameters['DrupalDBPassword'] = di_tot($iwParameters, 'DrupalDBPassword', '');
$iwParameters['DrupalDBName'] = di_tot($iwParameters, 'DrupalDBName', 'drupal');
$iwParameters['DrupalDBPrefix'] = di_tot($iwParameters, 'DrupalDBPrefix', 'drupal_');


/* This can be moved/copied to LocalSettings.php to override
values that are set here above. This might be needed if you 
have multiple web sites and want to reuse the code of this extension
from one place.

    $iwParameters['DrupalDBServer'] = "localhost";
    $iwParameters['DrupalDBUser'] = "drupaladmin";
    $iwParameters['DrupalDBPassword'] = "drupal";
    $iwParameters['DrupalDBName'] = "drupal66";
    $iwParameters['DrupalDBPrefix'] = "";
*/
