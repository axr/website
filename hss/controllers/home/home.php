<?php

namespace Hssdoc;

class HomeController extends Controller
{
	public function run ()
	{
		$view = new \Core\View(ROOT . '/views/home.html');
		$view->load_from_cache();

		$view->sidebar = (string) new SidebarView();
		$view->breadcrumb = $this->breadcrumb->get_rendered();

		$this->layout->title = 'HSS documentation';
		$this->layout->content = $view->get_rendered();

		echo $this->layout->get_rendered();
	}
}
