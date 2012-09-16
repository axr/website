<?php

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
	'run' => 'runAdd',
	'args' => array(1)
));

$router->route('/^\/page\/add\/(\w+)\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runEdit',
	'args' => array('add', 1)
));

$router->route('/^\/page\/(\d+)\/edit\/?$/', array(
	'controller' => 'PageController',
	'run' => 'runEdit',
	'args' => array('edit', 1)
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

$router->route('/^\/doc\/add_object\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_edit_object',
	'args' => array('add')
));

$router->route('/^\/doc\/add_property\/([^\/]+)\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_edit_property',
	'args' => array('add', 1)
));

$router->route('/^\/doc\/edit_property\/(\d+)\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_edit_property',
	'args' => array('edit', 1)
));

$router->route('/^\/doc\/([^\/]+)\/edit\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_edit_object',
	'args' => array('edit', 1)
));

$router->route('/^\/doc\/([^\/]+)\/rm\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_rm_object',
	'args' => array(1)
));

$router->route('/^\/doc\/([^\/]+)\/([^\/]+)\/rm\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_rm_property',
	'args' => array(1, 2)
));

$router->route('/^\/doc\/property_values\.json$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_property_values_GET',
	'method' => 'GET'
));

$router->route('/^\/doc\/property_values\.json$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_property_values_POST',
	'method' => 'POST'
));

$router->route('/^\/doc\/property_values\.json$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_property_values_DELETE',
	'method' => 'DELETE'
));

$router->route('/^\/doc\/([^\/#]+)\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run_object',
	'args' => array(1)
));

$router->route('/^\/doc\/?$/i', array(
	'controller' => 'HssdocController',
	'run' => 'run'
));

$router->route('/^\/downloads\/?$/i', array(
	'controller' => 'DownloadsController',
	'run' => 'run'
));

$router->route('/^\/admin\/cache\/?$/', array(
	'controller' => 'AdminController',
	'run' => 'runCache'
));

$router->route('/^\/(.+)$/', array(
	'controller' => 'PageController',
	'run' => 'runDisplay',
	'args' => array(1, 'url')
));

