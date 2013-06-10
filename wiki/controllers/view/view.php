<?php

namespace Wiki;

class ViewController extends Controller
{
	public function run ($view_file, $title = null)
	{
		$view = new \Core\View($view_file);
		$view->load_from_cache();

		$this->breadcrumb->push($title, null);

		$this->layout->title = $title;
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}
}
