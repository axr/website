<?php

namespace Hssdoc;

class HomeController extends Controller
{
	public function run ()
	{
		$this->view->_title = 'HSS documentation';
		unset($this->breadcrumb[count($this->breadcrumb) - 1]['link']);

		$this->view->sidebar = Sidebar::render();

		echo $this->render_view(ROOT . '/views/hssdoc.html');
	}
}
