<?php

require_once(ROOT . '/lib/www_controller.php');

class ViewController extends WWWController
{
	public function run ($view, $title = null)
	{
		$this->view->_title = $title;
		$this->breadcrumb[] = array(
			'name' => $title
		);

		echo $this->renderView($view);
	}
}

