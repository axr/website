<?php

define('ROOT', dirname(__FILE__) . '/..');
define('SHARED', ROOT . '/..');

require_once(SHARED . '/lib/core/http_exception.php');
require_once(SHARED . '/lib/core/config.php');
require_once(SHARED . '/lib/core/session.php');
require_once(SHARED . '/lib/core/router.php');
require_once(ROOT . '/controllers/view/view.php');

// Load configs
require_once(SHARED . '/config.php');
require_once(ROOT . '/config.php');

// Connect to the database
$dbh = new PDO(Config::get('/www/db/dsn'),
	Config::get('/www/db/user'),
	Config::get('/www/db/pass'));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Initialize the session
Session::initialize($dbh);

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

$router->route('/^\/get-involved(\/|$)/', array(
	'controller' => 'GetInvolvedController'
));

$goto = $router->find();
$_GET = $router->query;

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
	call_user_func_array(array($controller, 'run'), $goto[1]);
}
catch (HTTPException $e)
{
	if ($e->getCode() === 404)
	{
		$controller = new ViewController();
		$controller->initialize();
		$controller->run(SHARED . '/views/404.html');
	}
	else
	{
		echo $e->getCode() . ': ' . $e->getMessage();
	}
}

// Save the session
Session::save();

