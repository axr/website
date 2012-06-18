<?php

define('DRUPAL_ROOT', getcwd());

// Bootstrap drupal
require_once('./includes/bootstrap.inc');
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

/**
 * Make sure that the short changelog is available in cache
 *
 * @param string $sha
 */
function axr_cron_ensure_changelog_short ($sha)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,
		'https://api.github.com/repos/AXR/Prototype/git/tags/'.$sha);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);
	curl_close($ch);

	$response = json_decode($response);

	if (!isset($response->object))
	{
		return;
	}

	$changelog = str_replace("\r", "\n", "\n" . $response->message);
	$changelog = preg_replace("/[\n]+/", "\n", $changelog);
	$changelog = explode("\nChangelog:\n", $changelog);
	$changelog = explode("\n-----BEGIN PGP SIGNATURE-----\n", $changelog[1]);
	$changelog = explode("\n", $changelog[0]);
	$changelog = array_filter($changelog);

	cache_set('axr:changelog_short:' . $sha, serialize($changelog), 'cache',
		CACHE_PERMANENT);
}

/**
 * Get list of releases and insert into cache
 */
function axr_cron_releases_raw ()
{
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

	for ($i = count($response) - 1, $got = 0; $i >= 0; $i--)
	{
		if ($got >= 15 || !isset($response[$i]))
		{
			break;
		}

		if (preg_match('/^refs\/tags\/(v([0-9.]+)\-stable)$/',
				$response[$i]->ref, $match))
		{

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL,
				'https://api.github.com/repos/AXR/Prototype/git/tags/' .
				$response[$i]->object->sha);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$tag = json_decode(curl_exec($ch));
			curl_close($ch);

			if ($tag === null || $tag === false)
			{
				$date = 0;
			}
			else
			{
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

axr_cron_releases_raw();

echo '0';

