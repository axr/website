<?php

require_once(SHARED . '/lib/extend.php');

class URL
{
	private static $parts = array('scheme', 'host', 'user', 'pass', 'path',
		'query', 'fragment');

	/**
	 * Is readonly?
	 *
	 * @var bool
	 */
	private $readonly = false;

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
	public function __construct ($url = null)
	{
		if (is_string($url))
		{
			$this->from_string($url);
		}
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
	 * Return a copy of this instance
	 *
	 * @return URL
	 */
	public function copy ()
	{
		return new URL($this->to_string());
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
	 * Set the URL as readonly
	 */
	public function set_readonly ()
	{
		$this->readonly = true;
	}

	/**
	 * Check, if the URL is readonly
	 *
	 * @return bool
	 */
	public function is_readonly ()
	{
		return $this->readonly;
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
		$url = http_build_url('', array(
			'scheme' => $this->scheme,
			'host' => $this->host,
			'user' => $this->user,
			'pass' =>  $this->pass,
			'path' => $this->path,
			'query' => http_build_query($this->query),
			'fragment' => $this->fragment
		));

		$url = preg_replace('/\?($|#)/', '$1', $url);

		return $url;
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
		}

		if (is_numeric($value) || is_bool($value))
		{
			$value = (string) ((int) $value);
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

		if ($this->is_readonly())
		{
			throw new \Core\Exceptions\URLReadonly();
		}

		$this->fragment = $value;

		return $this;
	}
}
