<?php

namespace Hssdoc;

class ViewController extends Controller
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
