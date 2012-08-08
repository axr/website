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

		if (isset($_GET['_forajax']) && $_GET['_forajax'] === '1')
		{
			$this->view->_ajax = true;

			echo json_encode(array(
				'status' => 0,
				'payload' => array(
					'rsrc_bundles' => array(
						'css/get_involved.css',
						'js/get_involved.js'
					),
					'html' => $this->renderViewOnly(
						ROOT . '/views/get_involved.html', true)
				)
			));
		}
		else
		{
			echo $this->renderView(ROOT . '/views/get_involved.html');
		}
	}
}

