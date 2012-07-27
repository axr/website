<?php

require_once(ROOT . '/lib/www_controller.php');

class GetInvolvedController extends WWWController
{
	public function run ()
	{
		echo $this->renderView(ROOT . '/views/get_involved.html', $this->view);
	}
}

