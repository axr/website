<?php

if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);
	define('HTTP_URL_JOIN_PATH', 2);
	define('HTTP_URL_JOIN_QUERY', 4);
	define('HTTP_URL_STRIP_USER', 8);
	define('HTTP_URL_STRIP_PASS', 16);
	define('HTTP_URL_STRIP_AUTH', 32);
	define('HTTP_URL_STRIP_PORT', 64);
	define('HTTP_URL_STRIP_PATH', 128);
	define('HTTP_URL_STRIP_QUERY', 256);
	define('HTTP_URL_STRIP_FRAGMENT', 512);
	define('HTTP_URL_STRIP_ALL', 1024);

	function http_build_url($url, $parts = array(),
		$flags = HTTP_URL_REPLACE, &$new_url = false)
	{
		$keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}

		// Parse the original URL
		$parse_url = parse_url($url);

		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
		{
			$parse_url['scheme'] = $parts['scheme'];
		}

		if (isset($parts['host']))
		{
			$parse_url['host'] = $parts['host'];
		}

		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
				{
					$parse_url[$key] = $parts[$key];
				}
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
				{
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				}
				else
				{
					$parse_url['path'] = $parts['path'];
				}
			}

			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
				{
					$parse_url['query'] .= '&' . $parts['query'];
				}
				else
				{
					$parse_url['query'] = $parts['query'];
				}
			}
		}

		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
			{
				unset($parse_url[$key]);
			}
		}

		$new_url = $parse_url;

		return 
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '');
	}
}

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

/**
 * Either return the corresponding value for $key, from $array, or
 * return the default value ($or)
 *
 * @param array $array
 * @param string $key
 * @param mixed $or default=null
 * @return mixed
 */
function array_key_or ($array, $key, $or = null)
{
	if (!is_array($array))
	{
		return $or;
	}

	return isset($array[$key]) ? $array[$key] : $or;
}

