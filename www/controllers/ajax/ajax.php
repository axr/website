<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(SHARED . '/lib/github_activity.php');

class AjaxController extends WWWController
{
	public function run ($mode)
	{
		try
		{
			if ($mode === 'template')
			{
				$this->template();
			}
			else if ($mode === 'ghactivity')
			{
				$this->ghactivity();
			}
			else
			{
				throw new Exception('unknown_mode');
			}
		}
		catch (Exception $e)
		{
			echo json_encode(array(
				'status' => 1,
				'error' => $e->getMessage()
			));
		}
	}

	public function template ()
	{
		if (!isset($_GET['name']))
		{
			throw new Exception('invalid_request');
		}

		$name = preg_replace('/[^a-z0-9-_]/i', '', $_GET['name']);
		$template = null;

		if (file_exists(ROOT . '/views/' . $name . '.html'))
		{
			$template = file_get_contents(ROOT . '/views/' . $name . '.html');
		}
		elseif (file_exists(SHARED . '/views/' . $name . '.html'))
		{
			$template = file_get_contents(SHARED . '/views/' . $name . '.html');
		}
		else
		{
			throw new Exception('template_not_found');
		}

		echo json_encode(array(
			'status' => 0,
			'payload' => array(
				'name' => $name,
				'template' => $template
			)
		));
	}

	public function ghactivity ()
	{
		$count = isset($_GET['count']) ? (int) $_GET['count'] : 9999;

		$events = GithubActivity::getActivity();
		$events = array_splice($events, 0, $count);

		foreach ($events as &$event)
		{
			$event->title = str_replace('{TIME}',
				format_time_ago($event->created_at), $event->title);
		}

		echo json_encode(array(
			'status' => 0,
			'payload' => array(
				'events' => $events
			)
		));
	}
}

