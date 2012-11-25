<?php

require_once(SHARED . '/lib/core/url.php');

class Router
{
	/**
	 * @var Router
	 */
	protected static $instance = null;

	/**
	 * Registered routes are stored here
	 *
	 * @var mixed[]
	 */
	protected $routes = array();

	/**
	 * Current URL
	 *
	 * @var URL
	 */
	public $url = null;

	/**
	 * Request method
	 *
	 * @var string
	 */
	public $request_method = 'GET';

	/**
	 * Initialize the router. Parse the request URL.
	 */
	public function __construct ($url)
	{
		$this->url = new URL($url);
		$this->url->set_readonly();

		if (isset($_SERVER['REQUEST_METHOD']))
		{
			$this->request_method = $_SERVER['REQUEST_METHOD'];
		}

		if (self::$instance === null)
		{
			self::$instance = $this;
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
	 * Get instance
	 */
	public static function get_instance ()
	{
		return self::$instance;
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

		$args['regex'] = $regex;
		$this->routes[] = $args;

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
		foreach ($this->routes as $i => $args)
		{
			$regex = $args['regex'];

			if (isset($args['domain']) &&
				$args['domain'] !== $this->url->host)
			{
				continue;
			}

			if (!(bool) preg_match($regex, $this->url->path, $match))
			{
				continue;
			}

			if (isset($args['method']) &&
				$args['method'] != $this->request_method)
			{
				continue;
			}

			$args['run'] = isset($args['run']) ? $args['run'] : 'run';

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

			return array($args['controller'], $args['run'], $args['args']);
		}

		return null;
	}

	/**
	 * Parse an URL
	 *
	 * @deprecated
	 * @param string $url
	 * @return StdClass
	 */
	public static function parseUrl ($url)
	{
		$parsed = (object) array_merge(array(
			'scheme' => null,
			'host' => null,
			'user' => null,
			'pass' => null,
			'path' => null,
			'query' => array(),
			'fragment' => null
		), parse_url($url));

		if (!is_array($parsed->query))
		{
			$parsed->query = self::parseQuery($parsed->query);
		}

		return $parsed;
	}

	/**
	 * Parse a query string
	 *
	 * @deprecated
	 * @param string @query
	 * @return string[]
	 */
	public static function parseQuery ($queryRaw)
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
	 * Build a URL
	 *
	 * @deprecated
	 * @param string $url
	 * @param string|array $parts
	 * @param bool $returnParsed
	 * @return string
	 */
	public static function buildUrl ($url, $parts = array(),
		$returnParsed = false)
	{
		$parts = (array) $parts;

		if (isset($parts['query']) && is_array($parts['query']))
		{
			$parts['query'] = self::buildQuery($parts['query']);
		}

		if ($returnParsed)
		{
			$new = array();
			http_build_url($url, $parts,
				HTTP_URL_JOIN_PATH | HTTP_URL_JOIN_QUERY, $new);

			$new = (object) array_merge(array(
				'scheme' => null,
				'host' => null,
				'user' => null,
				'pass' => null,
				'path' => null,
				'query' => array(),
				'fragment' => null
			), $new);

			if (!is_array($new->query))
			{
				$new->query = self::parseQuery($new->query);
			}

			return $new;
		}

		return http_build_url($url, $parts,
			HTTP_URL_JOIN_PATH | HTTP_URL_JOIN_QUERY);
	}

	/**
	 * Build a query string
	 *
	 * @deprecated
	 * @param mixed $query
	 * @return string
	 */
	public static function buildQuery ($query)
	{
		return http_build_query($query);
	}

	/**
	 * Get current URL in parsed form
	 *
	 * @deprecated
	 * @return StdClass
	 */
	public static function getUrl ()
	{
		return self::parseUrl($_SERVER['REQUEST_URI']);
	}

	/**
	 * Get domain from the URL. If domain name extraction fails, null
	 * is returned.
	 *
	 * @deprecated
	 * @param string $url
	 * @return string
	 */
	public static function getDomain ($url)
	{
		preg_match('/^https?:\/\/([^\/]+)\//', $url, $match);
		return isset($match) && isset($match[1]) ? $match[1] : null;
	}
}
