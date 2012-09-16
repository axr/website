<?php

require_once(ROOT . '/lib/www_controller.php');

class GetInvolvedController extends WWWController
{
	public function run ()
	{
		$this->view->_title = 'Get involved';
		$this->breadcrumb[] = array(
			'name' => 'Get involved'
		);

		if (file_exists(Config::get('/www/irc_count_file')))
		{
			$this->view->irc_count =
				file_get_contents(Config::get('/www/irc_count_file'));
		}

		echo $this->renderView(ROOT . '/views/get_involved.html');
	}
}

