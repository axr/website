<?php

require_once(ROOT . '/lib/www_controller.php');

class CalendarController extends WWWController
{
	public function run ()
	{
		$this->breadcrumb[] = array(
			'title' => 'Calendar'
		);

		echo $this->renderView(ROOT . '/views/calendar.html');
	}
}

