<?php

namespace WWW;

require_once(SHARED . '/lib/github_activity.php');

class AjaxController extends Controller
{
	public function run ($mode)
	{
		try
		{
			if ($mode === 'ghactivity')
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
			header('Content-Type: application/javascript');

			$callback = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['callback']);
			return $callback . '(' . $data . ');';
		}

		header('Content-Type: application/json');

		return $data;
	}
}
