<?php

namespace WWW;

class GetInvolvedController extends Controller
{
	public function run ()
	{
		$this->view->_title = 'Get involved';
		$this->breadcrumb[] = array(
			'name' => 'Get involved'
		);

		echo $this->renderView(ROOT . '/views/get_involved.html');
	}
}
