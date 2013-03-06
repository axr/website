<?php

namespace Hssdoc;

$hssdoc_domain = \URL::create(\Config::get()->url->hssdoc)->host;

$router->route('/^\/$/', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\Hssdoc\\HomeController',
	'run' => 'run'
));

$router->route('/^\/(@\w+)\/?$/', array(
	'domain' => $hssdoc_domain,
	'controller' => '\\Hssdoc\\ObjectController',
	'run' => 'run',
	'args' => array(1)
));
