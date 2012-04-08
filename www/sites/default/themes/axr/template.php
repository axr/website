<?php

/**
 * Allow themable breadcrumbs
 */
function axr_breadcrumb($data) {
	$html = '';

	if (!empty($data['breadcrumb'])) {
		$lastitem = count($data['breadcrumb']);

		foreach ($data['breadcrumb'] as $value) {
			// I don't think there are any better ways to do this
			preg_match('/^<a.* href="(.+)".*>(.+)<\/a>$/', $value, $match);

			if ($match === null || $match === false) {
				continue;
			}

			$html .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
			$html .= '<a href="'.$match[1].'" itemprop="url">';
			$html .= '<span itemprop="title">'.$match[2].'</span>';
			$html .= '</a>';
			$html .= '</div>';
			$html .= '<span class="extra_0"></span>';
		}

		$html .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
		$html .= '<span class="current" itemprop="title">'.drupal_get_title().'</span>';
		$html .= '</div>';
	}

	return $html;
}

/**
 * As the name says: Preprocess html
 */
function axr_preprocess_html(&$variables) {
	$options = array(
		'group' => JS_THEME,
	);

	drupal_add_js(drupal_get_path('theme', 'axr'). '/js/script.js', $options);
}

/**
 * Preprocess page
 */
function axr_preprocess_page(&$variables) {
}

/**
 * Preprocess node
 */
function axr_preprocess_node(&$variables) {
	$node = $variables['node'];

	// Load node-specific css files
	drupal_add_css(path_to_theme() . '/css/' . $node->type . '.css');
	drupal_add_js(path_to_theme() . '/js/' . $node->type . '.js');
}

/**
 * Load newer jQuery.
 */
function axr_js_alter(&$javascript) {
	// Loading from a widely-used CDN is good for performance
	$javascript['misc/jquery.js']['data'] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
}

/**
 * Remove unwanted CSS.
 */
function axr_css_alter(&$css) { 
	unset($css[drupal_get_path('module','system').'/system.menus.css']); 
	unset($css[drupal_get_path('module','system').'/system.messages.css']);
	unset($css[drupal_get_path('module','system').'/system.theme.css']);

	unset($css[drupal_get_path('module','comment').'/comment.css']);
	unset($css[drupal_get_path('module','field').'/theme/field.css']);
	unset($css[drupal_get_path('module','node').'/node.css']);
	unset($css[drupal_get_path('module','search').'/search.css']);
	unset($css[drupal_get_path('module','user').'/user.css']);
}

