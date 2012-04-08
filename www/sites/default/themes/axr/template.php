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
	$releases = unserialize(cache_get('axr:releases:raw')->data);
	$data = array();

	if ($releases === false) {
		return array();
	}

	for ($i = $start; true; $i++)
	{
		if (!isset($releases[$i]) || $got >= $count)
		{
			break;
		}

		$version = $releases[$i]->version;
		$release = (object) array(
			'date' => 'n/a',
			'version' => $version,
			'url' => 'http://files.axr.vg/prototype/'.$version.'-stable/'.
				'axr_'.$version.'_'.$os.'_'.$arch.'.'.$ext[$os]
,
			'os_str' => isset($oses[$os]) ? $oses[$os] : $os,
			'sha' => $releases[$i]->sha
		);

		if (!axr_get_release_exists($release->url))
		{
			continue;
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

