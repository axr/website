<?php

require_once(ROOT . '/lib/www_controller.php');

class HomeController extends WWWController
{
	public function run ()
	{
		$this->view->_breadcrumb = false;

		echo $this->renderView(ROOT . '/views/home.html');
	}
}

