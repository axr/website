<?php

require_once(SHARED . '/lib/mustache/src/mustache.php');

class SpecialMixedLogin extends SpecialPage
{
	function __construct ()
	{
		parent::__construct('MixedLogin');
	}

	function execute ($par)
	{
		// Initialize MW stuff
		$request = $this->getRequest();
		$out = $this->getOutput();
		$this->setHeaders();

		// Set titme
		$out->setPageTitle('Login/register to wiki');

		// Initialize rendering stuff
		$mustache = new \Mustache\Renderer();
		$template = file_get_contents(dirname(__FILE__) . '/view.html');
		$view = new StdClass();

		// Render
		$out->addHTML($mustache->render($template, $view));
	}
}
