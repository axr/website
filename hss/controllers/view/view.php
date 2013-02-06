<?php

namespace Hssdoc;

class ViewController extends Controller
{
	public function run ($view, $title = null)
	{
		$this->view->_title = $title;

		unset($this->breadcrumb[count($this->breadcrumb) - 1]['link']);
		$this->breadcrumb[] = array(
			'name' => $title
		);

		echo $this->render_view($view);
	}
}
