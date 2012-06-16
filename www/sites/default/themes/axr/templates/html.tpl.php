<?php

require_once(SHARED . '/lib/mustache.php/Mustache.php');
require_once(SHARED . '/lib/axr/minify.php');

$mustache = new Mustache();
$view = axr_get_view();

// Basic stuff
$view->_title = $head_title;
$view->_content = $page;

// Resources
$view->_rsrc_styles = $styles;
$view->_rsrc_scripts = $scripts;

// Additional HTML (This crap was created just for Drupal)
$view->_html_head = $head;
$view->_html_top = $page_top;
$view->_html_bottom = $page_bottom;
$view->_html_body_classes = $classes;
$view->_html_body_attrs = $attributes;

if ($user->uid == 0)
{
	$view->_user = false;
}
else
{
	$view->_user = new StdClass();
	$view->_user->wiki_name = $user->name;
}

if (isset($view->_breadcrumb))
{
	// Get the breadcrumb
	$bc = $view->_breadcrumb;
	$bc = explode("\n", $bc);
	$view->_breadcrumb = array();

	// Parse the breadcrumb
	foreach ($bc as &$block)
	{
		$block = explode("\x00", $block);

		if (count($block) !== 2)
		{
			continue;
		}

		list($name, $link) = $block;
		$view->_breadcrumb[] = array(
			'link' => $link,
			'name' => $name
		);
	}

	// Set the last link active
	$view->_breadcrumb[count($view->_breadcrumb) - 1]['current'] = true;
}

$html = $mustache->render(
	file_get_contents(SHARED . '/views/layout.html'), $view);

echo Minify::html($html);

