<?php

namespace WWW;

define('ROOT', dirname(__FILE__) . '/..');
define('SHARED', ROOT . '/..');

// Set timezone
date_default_timezone_set('UTC');

// Initialize the benchmark
require_once(SHARED . '/lib/core/benchmark.php');
\Core\Benchmark::initialize();

require_once(SHARED . '/lib/extend.php');
require_once(SHARED . '/lib/core/autoloader.php');
require_once(SHARED . '/lib/gitdata/gitdata.php');
require_once(ROOT . '/lib/autoloader.php');
require_once(ROOT . '/controllers/view/view.php');

// Load configs
require_once(SHARED . '/config.php');
require_once(ROOT . '/config.php');

\Core\Benchmark::initialize();
\GitData\GitData::initialize(SHARED . '/data');

try
{
	// Initialize the cache
	\Cache::initialize(\Config::get('/shared/cache_servers'));
}
catch (\Core\Exceptions\MemcacheFailure $e)
{
	echo 'Could not establish connection to the cache server';
	exit;
}

// Create new router
$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$router = new \Router('http://' . $_SERVER['SERVER_NAME'] . $path);

// Register routes
require_once(ROOT . '/routes.php');

$goto = $router->find();
$_GET = $router->url->query;

header('Content-Type: text/html; charset=utf-8');

try
{
	if ($goto === null || !class_exists($goto[0]))
	{
		throw new \HTTPException(null, 404);
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
catch (\HTTPAjaxException $e)
{
	echo json_encode(array(
		'status' => $e->getCode(),
		'error' => $e->getMessage()
	));
}
catch (\HTTPException $e)
{
	$code_responses = array(
		400 => 'HTTP/1.0 400 Bad Request',
		403 => 'HTTP/1.0 403 Forbidden',
		404 => 'HTTP/1.0 404 Not Found'
	);

	if (isset($code_responses[$e->getCode()]))
	{
		header($code_responses[$e->getCode()]);
	}

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
