<?php

/**
 * Allow themable breadcrumbs
 */
function axr_breadcrumb ($data)
{
	$html = '';
	$breadcrumb = array();

	foreach ($data['breadcrumb'] as $link)
	{
		preg_match('/^<a.* href="(.+)".*>(.+)<\/a>$/', $link, $match);

		if (!is_array($match))
		{
			continue;
		}

		$breadcrumb[] = array($match[2], $match[1]);
	}

	$breadcrumb[] = array(drupal_get_title(), null);

	// I don't think there is a better way for doing this
	if (preg_match('/^\/blog\/[0-9]+\/[0-9]+\/.+/', request_uri()))
	{
		$breadcrumb[2] = null;
	}
	else if (preg_match('/^\/comment\/[0-9]+/', request_uri()))
	{
		$breadcrumb[2] = null;
	}
	else if (preg_match('/^\/user\/(login|register)(\/|\?|$)/', request_uri(), $match))
	{
		if ($match[1] === 'login')
		{
			$breadcrumb[1] = array('Login', null);
			$breadcrumb[2] = null;
		}
		else
		{
			$breadcrumb[1] = array('Register', null);
			$breadcrumb[2] = null;
		}
	}

	foreach ($breadcrumb as $link)
	{
		if ($link === null)
		{
			continue;
		}

		$html .= '<div itemscope itemtype="http://data-vocabulary.org/Breadcrumb">';

		if ($link[1] === null)
		{
			$html .= '<span class="current" itemprop="title">' .
				$link[0] . '</span>';
		}
		else
		{
			$html .= '<a href="' . $link[1] . '" itemprop="url">';
			$html .= '<span itemprop="title">' . $link[0] . '</span>';
			$html .= '</a>';
		}

		$html .= '</div>';
		$html .= ($link[1] !== null) ? '<span class="extra_0"></span>' : '';
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

	// TODO: Find a way to include it only on pages that have comments
	drupal_add_css($path . '/css/comments.css', array('group' => CSS_THEME));
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
	unset($css[drupal_get_path('module','filter').'/filter.css']);
	unset($css[drupal_get_path('module','user').'/user.css']);
}

