<?php

require_once(ROOT . '/lib/www_controller.php');

class HomeController extends WWWController
{
	public function run ()
	{
		echo $this->renderView(ROOT . '/views/home.html');
	}
}

