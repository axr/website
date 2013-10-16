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

$router->route('/^\/get-involved\/?$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\ViewController',
	'args' => array(ROOT . '/views/get_involved.html', 'Get Involved')
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

$router->route('/^\/(.+)$/', array(
	'domain' => $www_domain,
	'controller' => '\\WWW\\PageController',
	'run' => 'run_display',
	'args' => array(1)
));
