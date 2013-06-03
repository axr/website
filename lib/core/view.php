<?php

namespace Core;

class View
{
	protected $template;
	protected $rendered;

	protected $twig;

	protected $on_before_render = array();
	protected $cache_conditions = array();
	protected $data = array();

	/**
	 * __construct
	 *
	 * @param string $path
	 */
	public function __construct ($path)
	{
		$this->template = file_get_contents($path);
		$this->cache_condition('__template', hash('sha1', $this->template));

		$loader = new \Twig_Loader_String();
		$this->twig = new \Twig_Environment($loader, array(
			'cache' => \Config::get()->cache_path->twig
		));

		$this->twig->addFunction(new \Twig_SimpleFunction('config', function ()
		{
			return \Config::get();
		}));
	}

	/**
	 * __set
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set ($key, $value)
	{
		$this->data[$key] = $value;
	}

	/**
	 * __get
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * __toString
	 *
	 * @return string
	 */
	public function __toString ()
	{
		return $this->get_rendered();
	}

	/**
	 * Register a callback to be called before rendering
	 *
	 * Callback arguments:
	 * - View $view
	 *
	 * @param function $callback
	 */
	public function on_before_render ($callback)
	{
		$this->on_before_render[] = $callback;
	}

	/**
	 * Set a cache condition
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function cache_condition ($key, $value)
	{
		$this->cache_conditions[$key] = $value;
		$this->_cache_id = null;
	}

	/**
	 * Get the ID that is used to uniquely identify this specific renderation
	 * of this view.
	 *
	 * @return string
	 */
	public function get_cache_id ()
	{
		ksort($this->cache_conditions);
		return '/view/' . hash('sha1', http_build_query($this->cache_conditions));
	}

	/**
	 * Get the rendered view.
	 *
	 * @return string
	 */
	public function get_rendered ()
	{
		if ($this->rendered === null)
		{
			$this->render();
		}

		return $this->rendered;
	}

	/**
	 * Render the view and update the cache
	 */
	public function render ()
	{
		foreach ($this->on_before_render as $callback)
		{
			$callback($this);
		}

		$this->rendered = $this->twig->render($this->template, $this->data);

		if ($this->get_cache_id() !== null)
		{
			\Cache::set($this->get_cache_id(), $this->rendered);
		}
	}

	/**
	 * Try to load a rendered view from the cache. The loaded renderation can
	 * be accessed via get_rendered() method.
	 *
	 * The return value indicates whether the loading succeeded or not.
	 *
	 * @return bool
	 */
	public function load_from_cache ()
	{
		//$this->rendered = \Cache::get($this->get_cache_id());
		return $this->rendered !== null;
	}

	/**
	 * Returns the Twig environment that is used for rendering this view
	 *
	 * @return \Twig_Environment
	 */
	public function twig ()
	{
		return $this->twig;
	}
}
