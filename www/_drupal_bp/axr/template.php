<?php

/**
 * Allow themable breadcrumbs
 */
function axr_breadcrumb($data) {
	$html = '';

	if (!empty($data['breadcrumb'])) {
		$lastitem = count($data['breadcrumb']);

		foreach($data['breadcrumb'] as $value) {
			$html .= '<a href="#">'.$value.'</a>';
			$html .= '<span class="extra_0"></span>';
		}

		$html .= '<span class="inactive">'.drupal_get_title().'</span>';
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

