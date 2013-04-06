<?php

namespace Wiki;

class ViewController extends Controller
{
	public function run ($view, $title = null)
	{
		$this->view->_title = $title;
		$this->breadcrumb[] = array(
			'name' => $title
		);

		echo $this->render_page($view);
	}
}
