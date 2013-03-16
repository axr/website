<?php

namespace Wiki;

$wiki_domain = \URL::create(\Config::get()->url->wiki)->host;

$router->route('/^\/$/', array(
	'domain' => $wiki_domain,
	'controller' => '\\Wiki\\PageController',
	'run' => 'run_display',
	'args' => array('main-page')
));

$router->route('/^\/index$/', array(
	'domain' => $wiki_domain,
	'controller' => '\\Wiki\\IndexController',
	'run' => 'run',
	'args' => array('')
));

$router->route('/^\/index\/(.*)$/', array(
	'domain' => $wiki_domain,
	'controller' => '\\Wiki\\IndexController',
	'run' => 'run',
	'args' => array(1)
));

$router->route('/^\/(.+)$/', array(
	'domain' => $wiki_domain,
	'controller' => '\\Wiki\\PageController',
	'run' => 'run_display',
	'args' => array(1)
));
