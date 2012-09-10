<?php

define('ROOT', dirname(__FILE__) . '/..');
define('SHARED', ROOT . '/..');

// Set timezone
date_default_timezone_set('UTC');

require_once(SHARED . '/lib/extend.php');
require_once(SHARED . '/lib/activerecord/ActiveRecord.php');
require_once(SHARED . '/lib/core/http_exception.php');
require_once(SHARED . '/lib/core/config.php');
require_once(SHARED . '/lib/core/session.php');
require_once(SHARED . '/lib/core/router.php');
require_once(SHARED . '/lib/core/cache.php');
require_once(ROOT . '/controllers/view/view.php');
require_once(ROOT . '/models/user.php');

// Load configs
require_once(SHARED . '/config.php');
require_once(ROOT . '/config.php');

// Connect to the database
ActiveRecord\Config::initialize(function($cfg)
{
	$cfg->set_model_directory(ROOT . '/models');
	$cfg->set_default_connection('default');
	$cfg->set_connections(array(
		'default' => Config::get('/www/db/connection')
	));
});

// Initialize the session
Session::initialize();

// Initialize the cache
Cache::initialize();

// Create new router
$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$router = new Router(Config::get('/shared/www_url') . $path);

// Register routes
require_once(ROOT . '/routes.php');

$goto = $router->find();
$_GET = $router->url->query;

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
	if ($e->getCode() === 404)
	{
		$controller = new ViewController();
		$controller->initialize();
		$controller->run(SHARED . '/views/404.html', 'Not Found');
	}
	else if ($e->getCode() === 403)
	{
		$controller = new ViewController();
		$controller->initialize();
		$controller->run(SHARED . '/views/403.html', 'Forbidden');
	}
	else
	{
		echo $e->getCode() . ': ' . $e->getMessage();
	}
}

// Save the session
Session::save();

