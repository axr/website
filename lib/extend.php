<?php

/**
 * Format dates in `x units ago` format
 *
 * @param int $timestamp
 * @return string
 */
function format_time_ago ($timestamp)
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

