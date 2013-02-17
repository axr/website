<?php

namespace WWW;

require_once(SHARED . '/lib/github_activity.php');

class AjaxController extends Controller
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
				throw new \Exception('unknown_mode');
			}
		}
		catch (\Exception $e)
		{
			echo self::respond_json(array(
				'status' => 1,
				'error' => $e->getMessage()
			));
		}
	}

	public function template ()
	{
		if (!isset($_GET['name']))
		{
			throw new \Exception('invalid_request');
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
			throw new \Exception('template_not_found');
		}

		echo self::respond_json(array(
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

		$events = \GithubActivity::getActivity();
		$events = array_splice($events, 0, $count);

		foreach ($events as &$event)
		{
			$event->title = str_replace('{TIME}',
				format_time_ago($event->created_at), $event->title);
		}

		echo self::respond_json(array(
			'status' => 0,
			'payload' => array(
				'events' => $events
			)
		));
	}

	/**
	 * Send a JSON response
	 *
	 * @param mixed $data
	 * @return string
	 */
	private static function respond_json ($data)
	{
		$data = json_encode($data);

		if (isset($_GET['callback']))
		{
			$callback = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['callback']);
			return $callback . '(' . $data . ');';
		}

		return $data;
	}
}
