<?php

namespace AXR;

require_once(SHARED . '/lib/axr/breadcrumb_view.php');

class Controller extends \Core\Controller
{
	/**
	 * The layout view
	 *
	 * @var \Core\View
	 */
	public $layout;

	/**
	 * The breadcrumb view
	 *
	 * @var \AXR\BreadcrumbView
	 */
	public $breadcrumb;

	/**
	 * __construct
	 */
	public function __construct ()
	{
		parent::__construct();

		$that = $this;

		$this->layout = new \Core\View(SHARED . '/views/layout.html');
		$this->breadcrumb = new \AXR\BreadcrumbView();

		$this->layout->on_before_render(function ($view) use ($that)
		{
			$view->rsrc_styles = $that->rsrc->get_styles_html();
			$view->rsrc_scripts = $that->rsrc->get_scripts_html();

			$view->breadcrumb = $that->breadcrumb->get_rendered();
		});

		$this->layout->config = \Config::get();
		$this->layout->social = \GitData\Models\GenericConfig::file('config.json')->social;

		$this->layout->versions = array(
			'code' => \Config::get()->version,
			'data' => git_commit_id(\GitData\GitData::commit())
		);

		$this->layout->year  = date('Y');
		$this->layout->meta = new \StdClass();
	}
}
