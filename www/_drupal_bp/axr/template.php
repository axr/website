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
	drupal_add_js('https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js', 'theme');
	// Here should be `<script>window.jQuery || document.write('<script src="resources/js/libs/jquery-1.6.2.min.js"><\/script>')</script>`, but I'm not sure how to do it the right way.
	drupal_add_js(drupal_get_path('theme', 'axr') . '/js/script.js', 'theme');

	$variables['scripts'] = drupal_get_js();
}

