<?php

/**
 * Allow themable breadcrumbs
 */
function axr_breadcrumb ($data)
{
	$html = '';

	if (!empty($data['breadcrumb']))
	{
		$lastitem = count($data['breadcrumb']);

		foreach ($data['breadcrumb'] as $value)
		{
			// I don't think there are any better ways to do this
			preg_match('/^<a.* href="(.+)".*>(.+)<\/a>$/', $value, $match);

			if ($match === null || $match === false)
			{
				continue;
			}

			$html .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
			$html .= '<a href="'.$match[1].'" itemprop="url">';
			$html .= '<span itemprop="title">'.$match[2].'</span></a>';
			$html .= '</div>';
			$html .= '<span class="extra_0"></span>';
		}

		$html .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';
		$html .= '<span class="current" itemprop="title">' .
			drupal_get_title() . '</span>';
		$html .= '</div>';
	}

	return $html;
}

/**
 * As the name says: Preprocess html
 */
function axr_preprocess_html (&$variables)
{
	$path = drupal_get_path('theme', 'axr');

	drupal_add_js($path.'/js/script.js', array('group' => JS_THEME));
	drupal_add_js($path.'/js/mustache.js', array('group' => JS_THEME));
	drupal_add_js($path.'/js/native.history.js', array('group' => JS_THEME));
	drupal_add_js($path.'/js/ajaxsite.js', array('group' => JS_THEME));
}

/**
 * Preprocess page
 */
function axr_preprocess_page (&$variables)
{
	$variables['ajaxsite_page'] =
		preg_match('/^\/search[\?\/$]/', request_uri());
}

/**
 * Preprocess node
 */
function axr_preprocess_node (&$variables)
{
	// Get URL alias
	$alias = drupal_get_path_alias($_GET['q']);
	$alias = str_replace('/', '_', $alias);
	$alias = str_replace('-', '_', $alias);

	// Construct suggestion
	$variables['theme_hook_suggestions'][] = 'node__bp__'.$alias;
}

/**
 * Implements hook_js_alter()
 */
function axr_js_alter (&$js)
{
	$js['misc/jquery.js']['data'] =
		'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
	$js['misc/jquery.js']['type'] = 'external';
}

/**
 * Remove unwanted CSS.
 */
function axr_css_alter (&$css)
{ 
	unset($css[drupal_get_path('module','system').'/system.menus.css']); 
	unset($css[drupal_get_path('module','system').'/system.theme.css']);
	unset($css[drupal_get_path('module','user').'/user.css']);
}

