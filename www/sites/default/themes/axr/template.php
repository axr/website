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
 * Preprocess node
 */
function axr_preprocess_node(&$variables) {
	$node = $variables['node'];

	if (!$node->teaser) {
		// Load node-specific css files
		drupal_add_css(path_to_theme() . '/css/' . $node->type . '.css');
	}
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

