<?php

class GithubActivity
{
	/**
	 * Fetch and parse GitHub activity via GitHub API
	 * Some boring events have been commented out
	 *
	 * @return mixed
	 */
	public static function fetchActivity ($page = 1)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/orgs/AXR/events?page=' . $page);
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

		for ($i = 0, $c = 999; $i < $c; $i++)
		{
			if (!isset($response[$i]))
			{
				break;
			}

			$event = $response[$i];
			$title = null;
			$body = null;

			if (in_array($event->repo->name, array('AXR/Website', 'AXR/Design-Sources')))
			{
				// People don't care about the website
				continue;
			}

			switch ($event->type)
			{
				case 'CreateEvent':
					if ($event->payload->ref_type == 'repository')
					{
						$title = '<a href="{ACTOR_URL}" target="_blank">{ACTOR}</a> created a <span>repository</span> <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
					}
					else if ($event->payload->ref_type == 'branch')
					{
						$title = '{ACTOR} created a <span>branch</span> <a href="{URL}" target="_blank">{NAME}</a> on <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
						$title = str_replace('{URL}', 'https://github.com/' .
							$event->repo->name . '/tree/' . $event->payload->ref, $title);
					}
					else // if ($event->payload->ref_type == 'tag')
					{
						$title = '{ACTOR} created a <span>tag</span> {NAME} on <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
						$title = str_replace('{TYPE}', $event->payload->ref_type, $title);
					}

					$title = str_replace('{NAME}', $event->payload->ref, $title);
				break;

				case 'DeleteEvent':
					$title = '<a href="{ACTOR_URL}" target="_blank">{ACTOR}</a> deleted <span>{TYPE}</span> <strong>{NAME}</string> &mdash; {TIME}';
					$title = str_replace('{TYPE}', $event->payload->ref_type, $title);
					$title = str_replace('{NAME}', $event->payload->ref, $title);
				break;

				case 'IssuesEvent':
					// TODO $event->payload->action == 'open' ? show issue title
					$title = '<a href="{ACTOR_URL}" target="_blank">{ACTOR}</a> <span>{ACTED}</span> <a href="{URL}" target="_blank">Issue #{NUMBER}</a> on <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
					$title = str_replace('{ACTED}', $event->payload->action, $title);
					$title = str_replace('{URL}', $event->payload->issue->html_url, $title);
					$title = str_replace('{NUMBER}', $event->payload->issue->number, $title);

					$body = substr($event->payload->issue->body, 0, 120) . '...';
				break;

				case 'MemberEvent':
					$title = '<a href="{ACTOR_URL}" target="_blank">{ACTOR}</a> <span>added</span> <a href="{USER_URL}" target="_blank">{USER}</a> as a collaborator on <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
					$title = str_replace('{USER_URL}', $event->payload->mspanber->html_url, $title);
					$title = str_replace('{USER}', $event->payload->mspanber->login, $title);
				break;

				case 'PullRequestEvent':
					$title = '<a href="{ACTOR_URL}" target="_blank">{ACTOR}</a> <span>{ACTED}</span> <a href="{URL}">pull request {NUMBER}</a> on <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
					$title = str_replace('{ACTED}', $event->payload->action, $title);
					$title = str_replace('{URL}', $event->payload->pull_request->html_url, $title);
					$title = str_replace('{NUMBER}', $event->payload->pull_request->number, $title);
				break;

				case 'PushEvent':
					$title = '<a href="{ACTOR_URL}" target="_blank">{ACTOR}</a> <span>pushed</span> to {BRANCH} at <a href="{REPO_URL}" target="_blank">{REPO}</a> &mdash; {TIME}';
					$title = str_replace('{BRANCH}',
						preg_replace('/^refs\/heads\//', '', $event->payload->ref), $title);
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

			$events[] = (object) array(
				'event' => $event->type,
				'created_at' => strtotime($event->created_at),
				'title' => $title,
				'body' => $body
			);
		}

		return $events;
	}

	/**
	 * Get GitHub activity from the cache. If no cache is available, fetch
	 * it from GitHub
	 *
	 * @return mixed
	 */
	public static function getActivity ()
	{
		$data = Cache::get('/gh_activity');

		if ($data === null)
		{
			$data = array_merge(array(),
				self::fetchActivity(),
				self::fetchActivity(2));

			Cache::set('/gh_activity', $data, array('expires' => 1800));
		}

		return $data;
	}
}
