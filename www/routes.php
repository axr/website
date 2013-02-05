<?php

namespace WWW;

$www_domain = \Config::get('/shared/www_url')->host;
$hssdoc_domain = \Config::get('/shared/hssdoc_url')->host;

$router->route('/^\/$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\HomeController'
));

$router->route('/^\/_ajax\/(\w+)(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AjaxController',
	'args' => array(1)
));

$router->route('/^\/auth\/(\w+)(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AuthController',
	'args' => array(1)
));

$router->route('/^\/auth(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AuthController'
));

$router->route('/^\/account\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AccountController'
));

$router->route('/^\/account\/oid_rm\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AccountController',
	'run' => 'run_oid_rm_POST',
	'method' => 'POST'
));

$router->route('/^\/get-involved\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\GetInvolvedController'
));

$router->route('/^\/calendar\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\ViewController',
	'args' => array(ROOT . '/views/calendar.html', 'Calendar')
));

$router->route('/^\/about\/manifesto\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\ViewController',
	'args' => array(ROOT . '/views/manifesto.html', 'Manifesto')
));

$router->route('/^\/blog\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\PageController',
	'run' => 'run_blog_list'
));

$router->route('/^\/add_object\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_edit_object',
	'args' => array('add')
));

$router->route('/^\/add_property\/([^\/]+)\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_edit_property',
	'args' => array('add', 1)
));

$router->route('/^\/edit_property\/(\d+)\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_edit_property',
	'args' => array('edit', 1)
));

$router->route('/^\/([^\/]+)\/edit\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_edit_object',
	'args' => array('edit', 1)
));

$router->route('/^\/([^\/]+)\/rm\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_rm_object',
	'args' => array(1)
));

$router->route('/^\/([^\/]+)\/([^\/]+)\/rm\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_rm_property',
	'args' => array(1, 2)
));

$router->route('/^\/property_values\.json$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_property_values_GET',
	'method' => 'GET'
));

$router->route('/^\/property_values\.json$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_property_values_POST',
	'method' => 'POST'
));

$router->route('/^\/property_values\.json$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_property_values_DELETE',
	'method' => 'DELETE'
));

$router->route('/^\/([^\/#]+)\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run_object',
	'args' => array(1)
));

$router->route('/^\/?$/i', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\WWW\\HssdocController',
	'run' => 'run'
));

$router->route('/^\/downloads\/?$/i', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\DownloadsController',
	'run' => 'run'
));

$router->route('/^\/admin\/cache\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AdminController',
	'run' => 'runCache'
));

$router->route('/^\/(.+)$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\PageController',
	'run' => 'run_display',
	'args' => array(1)
));
