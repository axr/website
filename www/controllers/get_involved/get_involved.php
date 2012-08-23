<?php

require_once(ROOT . '/lib/www_controller.php');

class GetInvolvedController extends WWWController
{
	public function initialize ()
	{
		$this->rsrc->loadBundle('css/get_involved.css');
		$this->rsrc->loadBundle('js/get_involved.js');
	}

	public function run ()
	{
		$this->view->_title = 'Get involved';
		$this->breadcrumb[] = array(
			'name' => 'Get involved'
		);

		echo $this->renderView(ROOT . '/views/get_involved.html');
	}
}

