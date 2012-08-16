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

		if (isset($_GET['_forajax']) && $_GET['_forajax'] === '1')
		{
			$this->view->_ajax = true;

			echo json_encode(array(
				'status' => 0,
				'payload' => array(
					'html' => $this->renderViewOnly($view, true)
				)
			));
		}
		else
		{
			echo $this->renderView($view);
		}
	}
}

