<?php

/**
 * Get the view object
 *
 * @return SharedView
 */
function axr_get_view ()
{
	static $view;

	require_once(SHARED . '/lib/axr/shared_view.php');

	if (!is_object($view))
	{
		$view = new SharedView();
	}

	return $view;
}

/**
 * Allow themable breadcrumbs
 */
function axr_breadcrumb ($data)
{
	$output = '';

	foreach ($data['breadcrumb'] as $item)
	{
		preg_match('/^<a.* href="(.+)".*>(.+)<\/a>$/', $item, $match);

		if (!is_array($match) || count($match) !== 3)
		{
			continue;
		}

		list(, $link, $name) = $match;

		$output = $name . "\x00" . $link . "\n";
	}

	$output .= drupal_get_title() . "\x00";

	return $output;
}

/**
 * Preprocess page
 */
function axr_preprocess_page (&$variables)
{
	// Initialize variables
	$variables['ajaxsite_page'] = false;
	$variables['ajaxsite_js'] = array();
	$variables['is_404'] = drupal_get_http_header('status') === '404 Not Found';

	// If we're on the search page
	if (preg_match('/^\/search[\?\/$]/', request_uri()))
	{
		$tplSearch = json_encode(ajaxutil_get_template('search'));
		$tplSearchOptions = json_encode(ajaxutil_get_template('search_options'));
		$tplSearchResults = json_encode(ajaxutil_get_template('search_results'));

		$info = ajaxutil_get_info(request_uri());
		$infoJSON = json_encode($info);

		$variables['ajaxsite_page'] = true;
		$variables['ajaxsite_js'][] = <<<JS
Ajaxsite.template.cache = Ajaxsite.template.cache || {};
Ajaxsite.template.cache.search = {$tplSearch};
Ajaxsite.template.cache.search_options = {$tplSearchOptions};
Ajaxsite.template.cache.search_results = {$tplSearchResults};
Ajaxsite.urlInfo.cache = Ajaxsite.urlInfo.cache || {};
Ajaxsite.urlInfo.cache['{$info->url}'] = {$infoJSON};
JS;
	}

	// Overwrite the breadcrumb
	if (isset($variables['node']) && $variables['node']->type === 'blog')
	{
		drupal_set_breadcrumb(array(
			l('Home', '<front>'),
			l('Blog', 'blog')
		));
	}
	else if (preg_match('/^\/user\/(login|register)(\/|\?|$)/', request_uri()))
	{
		drupal_set_breadcrumb(array(
			l('Home', '<front>')
		));
	}
	else if (preg_match('/^\/user\/(login|register|[0-9]+)(\/|\?|$)/',
			request_uri(), $match))
	{
		drupal_set_breadcrumb(array(
			l('Home', '<front>'),
			is_numeric($match[1]) ? l('User', 'user') : null
		));
	}
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
	$js = array(); // This is not used

	RSRC::loadScript('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
	RSRC::loadBundle('js/bundle_shared.js');
	RSRC::loadBundle('js/bundle_drupal.js');
	RSRC::loadBundle('js/bundle_www.js');
}

/**
 * Remove unwanted CSS.
 */
function axr_css_alter (&$css)
{
	$css = array(); // This is not used

	RSRC::loadBundle('css/bundle_shared.css');
	RSRC::loadBundle('css/bundle_drupal.css');
	RSRC::loadBundle('css/bundle_www.css');
}

