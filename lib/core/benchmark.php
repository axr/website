<?php

namespace Core;

class Benchmark
{
	/**
	 * Sart time
	 *
	 * @var float
	 */
	private static $start_time = 0;

	/**
	 * Marked times
	 *
	 * @var float[]
	 */
	private static $marks = array();

	/**
	 * Initialize benchmark
	 */
	public static function initialize ()
	{
		self::$start_time = microtime(true);
	}

	/**
	 * Mark current time
	 *
	 * @param string $name
	 */
	public static function mark ($name)
	{
		self::$marks[$name] = microtime(true);
	}

	/**
	 * Get the time of a mark
	 *
	 * @param string $name
	 * @return float
	 */
	public static function get ($name)
	{
		if (isset(self::$marks[$name]))
		{
			return self::$marks[$name];
		}

		return null;
	}

	/**
	 * Show all stats
	 *
	 * @return string
	 */
	public static function stats ()
	{
		$out = array();
		$last_time = self::$start_time;

		foreach (self::$marks as $name => $time)
		{
			$out[] .= '<tr>' .
				'<td>' . $name . '</td>' .
				'<td>' . (($time - self::$start_time) * 1000) . '</td>' .
				'<td>' . (($time - $last_time) * 1000) . '</td>' .
				'</tr>';
			$last_time = $time;
		}

		return '<table border="1">' .
			'<tr><th>Name</th><th>Time</th><th>Time since previous</th></tr>' .
			implode("\n", $out) .
			'</table>';
	}
}
