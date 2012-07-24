<?php

class Router
{
	/**
	 * Registered routes are stored here
	 *
	 * @var mixed[]
	 */
	protected $routes = array();

	/**
	 * Requested path name
	 *
	 * @var string
	 */
	public $pathname = null;

	/**
	 * Parsed query string
	 *
	 * @var mixed[]
	 */
	public $query = array();

	/**
	 * Initialize the router. Parse the request URL.
	 */
	public function __construct ($url)
	{
		$url = explode('?', $url);

		$this->pathname = $url[0];

		if (isset($url[1]))
		{
			$this->query = $this->parseQuery($url[1]);
		}
	}

	/**
	 * Prevent stuff from being changed
	 *
	 * @param string $variable
	 * @param mixed $value
	 */
	public function __set ($variable, $value)
	{
	}

	/**
	 * Register a route
	 *
	 * $args
	 * - string controller (required) class name for the controller
	 * - mixed[] args (optional) arguments to pass to the controller
	 *   If the argument is an integer, the corresponding match from the
	 *   regex will be used instead.
	 *
	 * @param string $regex
	 * @param mixed[] $args
	 * @return bool
	 */
	public function route ($regex, $args)
	{
		if (!isset($args['controller']))
		{
			return false;
		}

		$this->routes[$regex] = $args;

		return true;
	}

	/**
	 * Find a controller. If no controller is found, null will be returned.
	 *
	 * @param string $url
	 * @return mixed[] [controller name, arguments]
	 */
	public function find ()
	{
		foreach ($this->routes as $regex => $args)
		{
			if (!(bool) preg_match($regex, $this->pathname, $match))
			{
				continue;
			}

			if (!isset($args['args']) || !is_array($args['args']))
			{
				$args['args'] = array();
			}

			// Replace integers in $args['args'] with actual values
			for ($i = 0, $c = count($args['args']); $i < $c; $i++)
			{
				$replace = $args['args'][$i];

				if (!is_integer($replace))
				{
					continue;
				}
				
				$args['args'][$i] = isset($match[$replace]) ?
					$match[$replace] : null;
			}

			return array($args['controller'], $args['args']);
		}

		return null;
	}

	/**
	 * Parse a query string
	 *
	 * @param string @query
	 * @return string[]
	 */
	public function parseQuery ($queryRaw)
	{
		$query = array();
		$pairs = explode('&', $queryRaw);
		
		foreach ($pairs as $pair)
		{
			$pair = explode('=', $pair);

			if (count($pair) !== 2)
			{
				continue;
			}

			$key = str_replace('.', '_', $pair[0]);
			$query[$key] = rawurldecode($pair[1]);
		}

		return $query;
	}

	/**
	 * Get domain from the URL. If domain name extraction fails, null
	 * is returned.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function getDomain ($url)
	{
		preg_match('/^https?:\/\/([^\/]+)\//', $url, $match);
		return isset($match) && isset($match[1]) ? $match[1] : null;
	}
}

