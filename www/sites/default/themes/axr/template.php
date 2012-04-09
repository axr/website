<?php

/**
 * Make sure that the short changelog is available in cache
 *
 * @param string $sha
 */
function axr_cron_ensure_changelog_short($sha) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,
		'https://api.github.com/repos/AXR/Prototype/git/tags/'.$sha);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);
	curl_close($ch);

	$response = json_decode($response);

	if (!isset($response->object)) {
		return;
	}

	$changelog = str_replace("\r", "\n", "\n".$response->message);
	$changelog = preg_replace("/[\n]+/", "\n", $changelog);
	$changelog = explode("\nChangelog:\n", $changelog);
	$changelog = explode("\n", $changelog[1]);
	$changelog = array_filter($changelog);

	cache_set('axr:changelog_short:'.$sha, serialize($changelog), 'cache',
		CACHE_PERMANENT);
}

/**
 * Get list of releases and insert into cache
 */
function axr_cron_releases_raw() {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,
		'https://api.github.com/repos/AXR/Prototype/git/refs/tags');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);
	curl_close($ch);

	$response = json_decode($response);

	if (!is_array($response))
	{
		return;
	}

	$tags = array();

	for ($i = count($response), $got = 0; $i >= 0; $i--) {
		if ($got >= 15) {
			break;
		}

		if (preg_match('/^refs\/tags\/(v([0-9.]+)\-stable)$/',
				$response[$i]->ref, $match)) {

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,
				'https://api.github.com/repos/AXR/Prototype/git/tags/'.
				$response[$i]->object->sha);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$tag = json_decode(curl_exec($ch));
			curl_close($ch);

			if ($tag === null || $tag === false) {
				$date = 0;
			} else {
				$date = strtotime($tag->tagger->date);
			}

			$tags[] = (object) array(
				'version' => $match[2],
				'tag' => $match[1],
				'sha' => $response[$i]->object->sha,
				'date' => $date
			);

			// Prepare the changelog
			axr_cron_ensure_changelog_short($response[$i]->object->sha);

			$got++;
		}
	}

	cache_set('axr:releases:raw', serialize($tags), 'cache', CACHE_PERMANENT);
}

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

	for ($i = $start, $got = 0; true; $i++)
	{
		if (!isset($releases[$i]) || $got >= $count)
		{
			break;
		}

		$version = $releases[$i]->version;
		$timestamp = $releases[$i]->date;

		$release = (object) array(
			'date' => ((int) $timestamp == 0) ? 'n/a' :
				gmdate('Y/m/d', $timestamp),
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
 * Drupal cron hook
 */
function axr_cron() {
	axr_cron_releases_raw();
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

	if (file_exists(path_to_theme() . '/css/' . $node->type . '.css')) {
		drupal_add_css(path_to_theme() . '/css/' . $node->type . '.css');
	}

	if (file_exists(path_to_theme() . '/js/' . $node->type . '.js')) {
		drupal_add_css(path_to_theme() . '/js/' . $node->type . '.js');
	}
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

