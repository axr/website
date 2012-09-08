<?php

class URL
{
	private static $parts = array('scheme', 'host', 'user', 'pass', 'path',
		'query', 'fragment');

	/**
	 * URL scheme
	 *
	 * @var string
	 */
	private $scheme = null;

	/**
	 * Hostname
	 *
	 * @var string
	 */
	private $host = null;

	/**
	 * User
	 *
	 * @var string
	 */
	private $user = null;

	/**
	 * Password
	 *
	 * @var string
	 */
	private $pass = null;

	/**
	 * Path
	 *
	 * @var string
	 */
	private $path = null;

	/**
	 * Query
	 *
	 * @var string[]
	 */
	private $query = array();

	/**
	 * Fragment
	 *
	 * @var string
	 */
	private $fragment = null;

	/**
	 * Constructor
	 *
	 * @param string $url
	 */
	public function __construct ($url)
	{
		$this->from_string($url);
	}

	/**
	 * toString
	 *
	 * @return string
	 */
	public function __toString ()
	{
		return $this->to_string();
	}

	/**
	 * Getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		if (in_array($key, self::$parts))
		{
			return $this->{$key};
		}

		return null;
	}

	/**
	 * Create a new URL instance
	 *
	 * @param string $url
	 * @return URL
	 */
	public static function create ($url = null)
	{
		return new URL($url);
	}

	/**
	 * Load an URL from string
	 *
	 * @param string $url
	 * @return URL
	 */
	public function from_string ($url)
	{
		$parsed = parse_url($url);

		if (isset($parsed['query']))
		{
			parse_str($parsed['query'], $parsed['query']);
		}

		foreach ($parsed as $key => $item)
		{
			if ($key === 'query')
			{
				$this->{$key} = array_merge($this->{$key}, $item);
				continue;
			}

			$this->{$key} = $item;
		}

		return $this;
	}

	/**
	 * Build a string
	 *
	 * @return string
	 */
	public function to_string ()
	{
		return http_build_url('', array(
			'scheme' => $this->scheme,
			'host' => $this->host,
			'user' => $this->user,
			'pass' =>  $this->pass,
			'path' => $this->path,
			'query' => http_build_query($this->query),
			'fragment' => $this->fragment
		));
	}

	/**
	 * Getter/setter for URL scheme
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function scheme ($value = null)
	{
		if ($value === null)
		{
			return $this->scheme;
		}

		$this->scheme = $value;

		return $this;
	}

	/**
	 * Getter/setter for URL host
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function host ($value = null)
	{
		if ($value === null)
		{
			return $this->host;
		}

		$this->host = $value;

		return $this;
	}

	/**
	 * Getter/setter for URL user
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function user ($value = null)
	{
		if ($value === null)
		{
			return $this->user;
		}

		$this->user = $value;

		return $this;
	}

	/**
	 * Getter/setter for URL pass
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function pass ($value = null)
	{
		if ($value === null)
		{
			return $this->pass;
		}

		$this->pass = $value;

		return $this;
	}

	/**
	 * Getter/setter for URL path
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function path ($value = null)
	{
		if ($value === null)
		{
			return $this->path;
		}

		$this->path = $value;

		return $this;
	}

	/**
	 * Getter/setter for URL query
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return mixed
	 */
	public function query ($key = null, $value = null)
	{
		if ($key === null)
		{
			return $this->query;
		}

		if (is_string($value) || is_array($value))
		{
			$key = array($key => $value);
		}

		if (is_string($key))
		{
			parse_str($key, $key);
		}

		if (is_array($key))
		{
			$this->query = array_merge($this->query, $key);
		}

		return $this;
	}

	/**
	 * Getter/setter for URL fragment
	 *
	 * @param string $value
	 * @return mixed
	 */
	public function fragment ($value = null)
	{
		if ($value === null)
		{
			return $this->fragment;
		}

		$this->fragment = $value;

		return $this;
	}
}

