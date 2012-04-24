<?php

/**
 * Check, if release file exists
 *
 * @param string $url
 */
function axr_get_release_exists ($url)
{
	$url = str_replace('http://files.axr.vg/', '/var/dev/files/', $url);
	return file_exists($url);
}

/**
 * Detect user's OS.
 *
 * @return string osx|linux|win
 */
function axr_get_os() {
	if (preg_match('/Mac/', $_SERVER['HTTP_USER_AGENT'])) {
		return 'osx';
	}

	if (preg_match('/Linux/', $_SERVER['HTTP_USER_AGENT'])) {
		return 'linux';
	}

	return 'win';
}

/**
 * Get user's system architecture.
 *
 * @return string x86|x86-64
 */
function axr_get_arch() {
	if (preg_match('/WOW64|x86_64|x64/', $_SERVER['HTTP_USER_AGENT'])) {
		return 'x86-64';
	}

	return 'x86';
}

/**
 * Get list of releases to display in the template.
 *
 * @param int $start
 * @param int $count
 * @param string $force_os
 * @param string $force_arch
 * @return mixed
 */
function axr_get_releases($start = 0, $count = 1,
	$force_os = null, $force_arch = null) {
	$oses = array(
		'osx' => 'OSX',
		'linux' => 'Linux',
		'win' => 'Windows'
	);

	$ext = array(
		'osx' => 'dmg',
		'linux' => 'tar.gz',
		'win' => 'zip'
	);

	$os = ($force_os !== null) ? $force_os : axr_get_os();
	$arch = ($force_arch !== null) ? $force_arch : axr_get_arch();
	$data = array();

	$releases = cache_get('axr:releases:raw');

	if (!is_object($releases)) {
		return array();
	}

	$releases = unserialize($releases->data);

	if ($releases === false) {
		return array();
	}

	for ($i = $start, $got = 0; true; $i++)
	{
		if (!isset($releases[$i]) || $got >= $count)
		{
			break;
		}

		$version = $releases[$i]->version;
		$timestamp = $releases[$i]->date;

		$url = 'http://files.axr.vg/prototype/'.$version.'-stable/axr_'.$version.'_';

		$release = (object) array(
			'date' => ((int) $timestamp == 0) ? 'n/a' :
				gmdate('Y/m/d', $timestamp),
			'version' => $version,
			'url' => $url.$os.'_'.$arch.'.'.$ext[$os],
			'urls' => (object) array(
				'linux' => (object) array(
					'x86-64' => $url.'linux_x86-64.'.$ext['linux'],
					'x86' => $url.'linux_x86.'.$ext['linux']
				),
				'osx' => (object) array(
					'x86-64' => $url.'osx_x86-64.'.$ext['osx'],
					'x86' => $url.'osx_x86.'.$ext['osx']
				),
				'win' => (object) array(
					'x86-64' => $url.'win_x86-64.'.$ext['win'],
					'x86' => $url.'win_x86.'.$ext['win']
				),
			),
			'os_str' => isset($oses[$os]) ? $oses[$os] : $os,
			'sha' => $releases[$i]->sha
		);

		if (!axr_get_release_exists($release->url))
		{
			continue;
		}

		foreach ($release->urls as $os => $urls)
		{
			if (!axr_get_release_exists($urls->{'x86-64'}))
			{
				unset($release->urls->$os->{'x86-64'});
			}

			if (!axr_get_release_exists($urls->x86))
			{
				unset($release->urls->$os->x86);
			}
		}

		$data[] = $release;
		$got++;
	}

	if (count($data) == 0 && $force_arch === null)
	{
		$data = axr_get_releases($start, $count, $os,
			($arch == 'x86' ? 'x86-64' : 'x86'));
	}
	else if (count($data) == 0 && $os != 'win')
	{
		$data = axr_get_releases($start, $count, 'win');
	}

	return $data;
}

/**
 * Get short changelog
 *
 * @param string $sha
 * @return string[]
 */
function axr_get_changelog_short($sha = null) {
	if ($sha === null) {
		$latest = axr_get_releases(0, 1);
		
		if (count($latest) == 0) {
			return null;
		}

		$sha = $latest[0]->sha;
	}

	$changelog = unserialize(cache_get('axr:changelog_short:'.$sha)->data);

	return ($changelog === false) ? null : $changelog;
}

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
	$path = drupal_get_path('theme', 'axr');

	drupal_add_js($path.'/js/script.js', array('group' => JS_THEME));
	drupal_add_js($path.'/js/mustache.js', array('group' => JS_THEME));
	drupal_add_js($path.'/js/native.history.js', array('group' => JS_THEME));
	drupal_add_js($path.'/js/ajaxsite.js', array('group' => JS_THEME));
}

/**
 * Preprocess page
 */
function axr_preprocess_page(&$variables) {
	$variables['ajaxsite_page'] = false;

	if (preg_match('/^\/search[\?\/$]/', request_uri())) {
		drupal_add_css(drupal_get_path('theme', 'axr'). '/css/search.css', array(
			'group' => CSS_THEME
		));
		$variables['ajaxsite_page'] = true;
	}
}

/**
 * Preprocess node
 */
function axr_preprocess_node(&$variables) {
	$node = $variables['node'];

	// Get URL alias
	$alias = drupal_get_path_alias($_GET['q']);
	$alias = str_replace('/', '_', $alias);
	$alias = str_replace('-', '_', $alias);

	// Construct suggestion
	$variables['theme_hook_suggestions'][] = 'node__bp__'.$alias;

	$path = drupal_get_path('theme', 'axr');

	// Node type specific CSS
	if (file_exists($path.'/css/node--'.$node->type.'.css')) {
		drupal_add_css($path.'/css/node--'.$node->type.'.css', array(
			'group' => CSS_THEME
		));
	}

	// Node type specific JS
	if (file_exists($path.'/js/node--'.$node->type.'.js')) {
		drupal_add_js($path.'/js/node--'.$node->type.'.js', array(
			'group' => JS_THEME
		));
	}
	
	// URL alias specific CSS
	if (file_exists($path.'/css/node--bp--'.$alias.'.css')) {
		drupal_add_css($path.'/css/node--bp--'.$alias.'.css', array(
			'group' => CSS_THEME
		));
	}

	// URL alias specific JS
	if (file_exists($path.'/js/node--bp--'.$alias.'.js')) {
		drupal_add_js($path.'/js/node--bp--'.$alias.'.js', array(
			'group' => JS_THEME
		));
	}	
}

function axr_js_alter(&$js) {
	$js['misc/jquery.js']['data'] = 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
	$js['misc/jquery.js']['type'] = 'external';
}

/**
 * Remove unwanted CSS.
 */
function axr_css_alter(&$css) { 
	unset($css[drupal_get_path('module','system').'/system.menus.css']); 
	unset($css[drupal_get_path('module','system').'/system.theme.css']);
	unset($css[drupal_get_path('module','user').'/user.css']);
}

