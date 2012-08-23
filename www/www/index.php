<?php

define('ROOT', dirname(__FILE__) . '/..');
define('SHARED', ROOT . '/..');

// Set timezone
date_default_timezone_set('UTC');

require_once(SHARED . '/lib/extend.php');
require_once(SHARED . '/lib/core/http_exception.php');
require_once(SHARED . '/lib/core/config.php');
require_once(SHARED . '/lib/core/session.php');
require_once(SHARED . '/lib/core/router.php');
require_once(SHARED . '/lib/core/cache.php');
require_once(ROOT . '/controllers/view/view.php');

// Load configs
require_once(SHARED . '/config.php');
require_once(ROOT . '/config.php');

// Connect to the database
$dbh = new PDO(Config::get('/www/db/dsn'),
	Config::get('/www/db/user'),
	Config::get('/www/db/pass'),
	array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));

if (Config::get('/www/debug'))
{
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Initialize the session
Session::initialize($dbh);

// Initialize the cache
Cache::initialize($dbh);

// Create new router
$router = new Router(isset($_SERVER['REQUEST_URI']) ?
	$_SERVER['REQUEST_URI'] : '/');

// Register routes
$router->route('/^\/$/', array(
	'controller' => 'HomeController'
));

$router->route('/^\/_ajax\/(\w+)(\/|$)/', array(
	'controller' => 'AjaxController',
	'args' => array(1)
));

$router->route('/^\/auth\/(\w+)(\/|$)/', array(
	'controller' => 'AuthController',
	'args' => array(1)
));

$router->route('/^\/auth(\/|$)/', array(
	'controller' => 'AuthController'
));

$router->route('/^\/get-involved\/?$/', array(
	'controller' => 'GetInvolvedController'
));

$router->route('/^\/calendar\/?$/', array(
	'controller' => 'ViewController',
	'args' => array(ROOT . '/views/calendar.html', 'Calendar')
));

$router->route('/^\/page\/add\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runAddSelect',
	'args' => array(1)
));

$router->route('/^\/page\/add\/(\w+)\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runAdd',
	'args' => array(1)
));

$router->route('/^\/page\/(\w+)\/edit\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runEdit',
	'args' => array(1)
));

$router->route('/^\/page\/(\w+)\/rm\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runRm',
	'args' => array(1)
));

$router->route('/^\/page\/(\w+)\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runDisplay',
	'args' => array(1)
));

$router->route('/^\/blog\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runBlogList'
));

$router->route('/^\/doc\/([^\/#]+)\/?$/i', array(
	'controller' => 'PageController',
	'run' => 'runHssdocObj',
	'args' => array(1)
));

$router->route('/^\/doc\/?$/i', array(
	'controller' => 'PageController',
	'run' => 'runHssdoc'
));

$router->route('/^\/downloads\/?$/i', array(
	'controller' => 'DownloadsController',
	'run' => 'run'
));

$router->route('/^\/(.+)$/', array(
	'controller' => 'PageController',
	'run' => 'runDisplay',
	'args' => array(1)
));

$goto = $router->find();
$_GET = $router->query;

header('Content-Type: text/html; charset=utf-8');

try
{
	if ($goto === null)
	{
		throw new HTTPException(null, 404);
	}

	$name = preg_replace('/Controller$/', '', $goto[0]);
	$name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
	$path = ROOT . '/controllers/' . $name . '/' . $name . '.php';

	if (!file_exists($path))
	{
		throw new HTTPException(null, 404);
	}

	require_once($path);

	if (!class_exists($goto[0]))
	{
		throw new HTTPException(null, 404);
	}

	$controller = new $goto[0]();
	$controller->dbh = $dbh;

	function c ()
	{
		global $controller;
		return $controller;
	}

	call_user_func(array($controller, 'initialize'));
	call_user_func_array(array($controller, $goto[1]), $goto[2]);
}
catch (HTTPException $e)
{
	if (isset($_GET['_forajax']) || isset($_GET['_ajax']))
	{
		echo json_encode(array(
			'status' => 1,
			'error' => 'HTTPException:' . $e->getCode() . ':' . $e->getMessage()
		));
	}
	else
	{
		if ($e->getCode() === 404)
		{
			$controller = new ViewController();
			$controller->initialize();
			$controller->run(SHARED . '/views/404.html', 'Not Found');
		}
		else
		{
			echo $e->getCode() . ': ' . $e->getMessage();
		}
	}
}

// Save the session
Session::save();

