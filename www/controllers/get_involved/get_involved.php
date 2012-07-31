<?php

require_once(ROOT . '/lib/www_controller.php');

class GetInvolvedController extends WWWController
{
	public function run ()
	{
		$this->view->_title = 'Get involved';
		$this->view->_breadcrumb[] = array(
			'name' => 'Get involved'
		);

		echo $this->renderView(ROOT . '/views/get_involved.html', $this->view);
	}
}

