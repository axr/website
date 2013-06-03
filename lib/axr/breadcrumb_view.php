<?php

namespace AXR;

class BreadcrumbView extends \Core\View
{
	/**
	 * __construct
	 */
	public function __construct ()
	{
		parent::__construct(SHARED . '/views/layout_breadcrumb.html');

		$this->data['breadcrumb'] = array();
	}

	/**
	 * Render the breadcrumb
	 */
	public function render ()
	{
		if (count($this->data['breadcrumb']) > 1)
		{
			// Only render when there is something to render
			parent::render();
		}
	}

	/**
	 * Insert a link
	 *
	 * @param string $title
	 * @param string $link
	 */
	public function push ($title, $link)
	{
		$this->data['breadcrumb'][] = array(
			'title' => $title,
			'link' => $link
		);
	}

	/**
	 * Remove one or more links from the end
	 *
	 * @param string $title
	 * @param string $link
	 */
	public function pop ($n = 1)
	{
		if ($n > count($this->data['breadcrumb']))
		{
			$this->clear();
			return;
		}

		for ($i = 0; $i < $n; $i++)
		{
			array_pop($this->data['breadcrumb']);
		}
	}

	/**
	 * Remove all items
	 */
	public function clear ()
	{
		$this->data['breadcrumb'] = array();
	}
}
