<?php

require_once(ROOT . '/lib/www_controller.php');

class ViewController extends WWWController
{
	public function run ($view)
	{
		echo $this->renderView($view, $this->view);
	}
}

