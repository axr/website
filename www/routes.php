<?php

namespace WWW;

$www_domain = \URL::create(\Config::get()->url->www)->host;

$router->route('/^\/$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\HomeController'
));

$router->route('/^\/_ajax\/(\w+)(\/|$)/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\AjaxController',
	'args' => array(1)
));

$router->route('/^\/gitdata\/asset\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\GitDataController',
	'run' => 'run_asset'
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
