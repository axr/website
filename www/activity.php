<?php

/**
 * GitHub activity feed parser.
 *
 * @todo make the long lines shorter (max 80 characters)
 * @author Ragnis Armus
 */

define('DRUPAL_ROOT', getcwd());

// Bootstrap drupal
require_once('./includes/bootstrap.inc');
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

/**
 * Fetch GitHub activity feed for AXR organization.
 *
 * @return mixed parsed activity feed or false in case of failure
 */
function axr_get_github_activity ()
{
	if ($events = cache_get('axr:github_activity'))
	{
		if ($events->expire > time() &&
			$events = unserialize($events->data))
		{
			return $events;
		}
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/orgs/AXR/events');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);
	curl_close($ch);

	$response = json_decode($response);

	if ($response === null ||
		!is_array($response))
	{
		return false;
	}

	$events = array();

	for ($i = 0, $c = 5; $i < $c; $i++)
	{
		if (!isset($response[$i]))
		{
			break;
		}

		$event = $response[$i];
		$title = null;
		$body = null;

		switch ($event->type)
		{
			case 'CreateEvent':
				if ($event->payload->ref_type == 'repository')
				{
					$title = '<a href="{ACTOR_URL}">{ACTOR}</a> created a <span>repository</span> <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
				}
				else if ($event->payload->ref_type == 'branch')
				{
					$title = '{ACTOR} created a <span>branch</span> <a href="{URL}">{NAME}</a> on <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
					$title = str_replace('{URL}', 'https://github.com/' .
						$event->repo->name . '/tree/' . $event->payload->ref, $title);
				}
				else // if ($event->payload->ref_type == 'tag')
				{
					$title = '{ACTOR} created a <span>tag</span> {NAME} on <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
					$title = str_replace('{TYPE}', $event->payload->ref_type, $title);
				}

				$title = str_replace('{NAME}', $event->payload->ref, $title);
			break;

			case 'DeleteEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> deleted <span>{TYPE}</span> <strong>{NAME}</string> &mdash; {TIME}';
				$title = str_replace('{TYPE}', $event->payload->ref_type, $title);
				$title = str_replace('{NAME}', $event->payload->ref, $title);
			break;

			case 'DownloadEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> created a <span>download</span> <a href="{URL}">{NAME}</a> on <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
				$title = str_replace('{URL}', $event->payload->download->html_url, $title);
				$title = str_replace('{NAME}', $event->payload->download->name, $title);
			break;

			case 'ForkEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>forked</span> <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
			break;

			case 'GollumEvent':
				if (count($event->payload->pages) == 1)
				{
					$page = $event->payload->pages[0];
					$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>created</span> a <a href="{URL}">page</a> on the <a href="{REPO_URL}">{REPO}</a> wiki &mdash; {TIME}';
					$title = str_replace('{URL}', $page->html_url, $title);
				}
				else
				{
					$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>edited</span> the <a href="{REPO_URL}">{REPO}</a> wiki &mdash; {TIME}';

					$body = array();

					foreach ($event->payload->pages as $page)
					{
						if ($page->action == 'created')
						{
							$msg = 'Created <a href="{URL}">{PAGE}</a>';
						}
						else
						{
							$msg = 'Edited <a href="{URL}">{PAGE}</a>. <a href="{DIFF}">View the diff &raquo;</a>';
						}

						$msg = str_replace('{URL}', $page->html_url, $msg);
						$msg = str_replace('{PAGE}', $page->page_name, $msg);
						$msg = str_replace('{DIFF}', $page->html_url .
							'/_compare/' . $page->sha, $msg);

						$body[] = array(
							'msg' => $msg
						);
					}
				}
			break;

			case 'IssuesEvent':
				// TODO $event->payload->action == 'open' ? show issue title
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>{ACTED}</span> <a href="{URL}">Issue #{NUMBER}</a> &mdash; {TIME}';
				$title = str_replace('{ACTED}', $event->payload->action, $title);
				$title = str_replace('{URL}', $event->payload->issue->html_url, $title);
				$title = str_replace('{NUMBER}', $event->payload->issue->number, $title);

				$body = substr($event->payload->issue->body, 0, 120) . '...';
			break;

			case 'IssueCommentEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>commented</span> on <a href="{URL}">Issue #{NUMBER}</a> &mdash; {TIME}';
				$title = str_replace('{URL}', $event->payload->issue->html_url
					. '#issuecomment-' . $event->payload->comment->id, $title);
				$title = str_replace('{NUMBER}', $event->payload->issue->number, $title);

				$body = !empty($event->payload->comment->body) ? 
					substr($event->payload->comment->body, 0, 120) . '...' : null;
			break;

			case 'MemberberEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>added</span> <a href="{USER_URL}">{USER}</a> as a collaborator on <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
				$title = str_replace('{USER_URL}', $event->payload->mspanber->html_url, $title);
				$title = str_replace('{USER}', $event->payload->mspanber->login, $title);
			break;

			case 'PublicEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> made <span>repository</span> <a href="{REPO_URL}">{REPO}</a> public &mdash; {TIME}';
			break;

			case 'PullRequestEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>{ACTED}</span> <a href="{URL}">pull request {NUMBER}</a> on <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
				$title = str_replace('{ACTED}', $event->payload->action, $title);
				$title = str_replace('{URL}', $event->payload->pull_request->html_url, $title);
				$title = str_replace('{NUMBER}', $event->payload->pull_request->number, $title);
			break;

			case 'PushEvent':
				$title = '<a href="{ACTOR_URL}">{ACTOR}</a> <span>pushed</span> to {BRANCH} at <a href="{REPO_URL}">{REPO}</a> &mdash; {TIME}';
				$title = str_replace('{BRANCH}',
					preg_replace('/^\/refs\/heads\//', '', $event->payload->ref), $title);

				$body = array();

				foreach ($event->payload->commits as $commit)
				{
					// TODO Make #[0-9]+ automatically into issue links
					$msg = '<a href="{URL}">{SHA}</a> {MESSAGE}';
					$msg = str_replace('{URL}', 'https://github.com/' .
						$event->repo->name . '/commit/' . $commit->sha, $msg);
					$msg = str_replace('{SHA}', substr($commit->sha, 0, 7), $msg);
					$msg = str_replace('{MESSAGE}', substr($commit->message, 0, 80), $msg);

					$body[] = array(
						'msg' => $msg
					);
				}
			break;

			default:
			$c++;
			continue(2);
		}

		// Commonly used variables that every event has
		$title = str_replace('{ACTOR_URL}', 'https://github.com/' . $event->actor->login, $title);
		$title = str_replace('{ACTOR}', $event->actor->login, $title);
		$title = str_replace('{REPO_URL}', 'https://github.com/' . $event->repo->name, $title);
		$title = str_replace('{REPO}', $event->repo->name, $title);

		$events[] = array(
			'event' => $event->type,
			'created_at' => strtotime($event->created_at),
			'title' => $title,
			'body' => $body
		);
	}

	cache_set('axr:github_activity', serialize($events), 'cache', time() + (60 * 10));

	return $events;
}

/**
 * Format time to `x units ago` format.
 * The code is taken from http://www.devnetwork.net/viewtopic.php?f=50&t=113253#p595063
 *
 * @param int $timestamp
 * @return string formatted date
 */
function axr_format_time_ago ($timestamp)
{
	$diff = time() - $timestamp;

	if ($diff == 0)
	{
		return;
	}

	$intervals = array(
		true => array('year', 31556926),
		$diff < 31556926 => array('month', 2628000),
		$diff < 2629744 => array('week', 604800),
		$diff < 604800 => array('day', 86400),
		$diff < 86400 => array('hour', 3600),
		$diff < 3600 => array('minute', 60),
		$diff < 60 => array('second', 1)
	);

	$value = floor($diff / $intervals[true][1]);

	return $value . ' ' . $intervals[true][0] .
		($value > 1 ? 's' : '') . ' ago';
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

$activity = axr_get_github_activity();

if ($activity === false)
{
	echo json_encode(array(
		'error' => 1,
		'page' => $page,
		'activity' => null
	));
}
else
{
	foreach ($activity as &$event)
	{
		$event['title'] = str_replace('{TIME}',
			axr_format_time_ago($event['created_at']), $event['title']);
	}

	echo json_encode(array(
		'error' => 0,
		'activity' => $activity
	));
}
