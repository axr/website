<?php

$www_domain = Config::get('/shared/www_url')->host;
$hssdoc_domain = Config::get('/shared/hssdoc_url')->host;

$router->route('/^\/$/', array(
	'domain' => $www_domain,
	'controller' => 'HomeController'
));

$router->route('/^\/_ajax\/(\w+)(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => 'AjaxController',
	'args' => array(1)
));

$router->route('/^\/auth\/(\w+)(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => 'AuthController',
	'args' => array(1)
));

$router->route('/^\/auth(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => 'AuthController'
));

$router->route('/^\/get-involved\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'GetInvolvedController'
));

$router->route('/^\/calendar\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'ViewController',
	'args' => array(ROOT . '/views/calendar.html', 'Calendar')
));

$router->route('/^\/about\/manifesto\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'ViewController',
	'args' => array(ROOT . '/views/manifesto.html', 'Manifesto')
));

$router->route('/^\/page\/add\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runAdd',
	'args' => array(1)
));

$router->route('/^\/page\/add\/(\w+)\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runEdit',
	'args' => array('add', 1)
));

$router->route('/^\/page\/(\d+)\/edit\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runEdit',
	'args' => array('edit', 1)
));

$router->route('/^\/page\/(\w+)\/rm\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runRm',
	'args' => array(1)
));

$router->route('/^\/page\/(\d+)\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runDisplay',
	'args' => array(1, 'id')
));

$router->route('/^\/blog\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runBlogList'
));

$router->route('/^\/add_object\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_edit_object',
	'args' => array('add')
));

$router->route('/^\/add_property\/([^\/]+)\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_edit_property',
	'args' => array('add', 1)
));

$router->route('/^\/edit_property\/(\d+)\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_edit_property',
	'args' => array('edit', 1)
));

$router->route('/^\/([^\/]+)\/edit\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_edit_object',
	'args' => array('edit', 1)
));

$router->route('/^\/([^\/]+)\/rm\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_rm_object',
	'args' => array(1)
));

$router->route('/^\/([^\/]+)\/([^\/]+)\/rm\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_rm_property',
	'args' => array(1, 2)
));

$router->route('/^\/property_values\.json$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_property_values_GET',
	'method' => 'GET'
));

$router->route('/^\/property_values\.json$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_property_values_POST',
	'method' => 'POST'
));

$router->route('/^\/property_values\.json$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_property_values_DELETE',
	'method' => 'DELETE'
));

$router->route('/^\/([^\/#]+)\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run_object',
	'args' => array(1)
));

$router->route('/^\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => 'HssdocController',
	'run' => 'run'
));

$router->route('/^\/downloads\/?$/i', array(
	'domain' => $www_domain,
	'controller' => 'DownloadsController',
	'run' => 'run'
));

$router->route('/^\/admin\/cache\/?$/', array(
	'domain' => $www_domain,
	'controller' => 'AdminController',
	'run' => 'runCache'
));

$router->route('/^\/(.+)$/', array(
	'domain' => $www_domain,
	'controller' => 'PageController',
	'run' => 'runDisplay',
	'args' => array(1, 'url')
));
