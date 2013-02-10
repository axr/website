<?php

namespace Core;

require_once(SHARED . '/lib/mustache/src/mustache.php');
require_once(SHARED . '/lib/mustache_filters/json.php');

\Mustache\Filter::register(new \MustacheFilters\JSON);

class Controller
{
	/**
	 * View variables are kept here
	 *
	 * @var StdClass
	 */
	protected $view;

	/**
	 * Resource loader
	 *
	 * @var RSRC
	 */
	public $rsrc;

	/**
	 */
	public $tabs = array();

	/**
	 */
	public $breadcrumb = array();

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$this->view = new \StdClass();
		$this->rsrc = new \RSRC();

		$this->view->_POST = $_POST;
		$this->view->_GET = $_GET;
	}

	/**
	 * Initialize callback
	 */
	public function initialize ()
	{
	}

	/**
	 * Get an already rendered page from the cache. If nothing is found, `null`
	 * is returned.
	 *
	 * @param string $cache_key
	 * @return string
	 */
	protected function get_cached_page ($cache_key)
	{
		return \Cache::get('/_prerendered_page/' . hash('sha1', $cache_key));
	}

	/**
	 * Render a view
	 *
	 * @deprecated use render_view instead
	 * @param string $view_path
	 * @return string
	 */
	protected function renderView ($view_path)
	{
		return $this->render_view($view_path);
	}

	/**
	 * Render a view
	 *
	 * Options:
	 * - string cache_key: An unique identifier for the page for caching
	 * - mixed[] cache_options
	 *
	 * @param string $view_path
	 * @param mixed[] $options
	 * @return string
	 */
	protected function render_view ($view_path, array $options = array())
	{
		$layout_path = SHARED . '/views/layout.html';

		$this->view->{'g/content'} = $this->render_simple_view($view_path, $this->view);
		$html = $this->render_simple_view($layout_path, $this->view, array(
			'minify' => true,
			'fallback_template' => '{{{g/content}}}'
		));

		if (isset($options['cache_key']) && is_string($options['cache_key']))
		{
			$cache_key = '/_prerendered_page/' . hash('sha1', $options['cache_key']);
			$cache_options = array(
				'data_version' => 'current'
			);

			if (isset($options['cache_options']) &&
				is_array($options['cache_options']))
			{
				$cache_options = array_merge($cache_options,
					$options['cache_options']);
			}

			\Cache::set($cache_key, $html, $cache_options);
		}

		return $html;
	}

	/**
	 * Render just the view. Don't wrap it with the layout
	 *
	 * @deprecated use render_simple_view instead
	 */
	public function renderViewOnly ($file, $minify = false)
	{
		return $this->render_simple_view($file, $this->view, array(
			'minify' => $minify
		));
	}

	/**
	 * Render simple view.
	 *
	 * @param string $path
	 * @param \StdClass $view
	 * @return string
	 */
	public function render_simple_view ($path, \StdClass $view, array $options = array())
	{
		$extension = pathinfo($path, PATHINFO_EXTENSION);

		if (file_exists($path))
		{
			$template = file_get_contents($path);
		}
		elseif (isset($options['fallback_template']))
		{
			// We will intentionally still use the extension from the original
			// path.

			$template = $options['fallback_template'];
		}
		else
		{
			return null;
		}

		$mustache = new \Mustache\Renderer();
		$html = $mustache->render($template, $view);

		if (isset($options['minify']) && $options['minify'] === true)
		{
			// Templates are not always HTML
			if ($extension === 'html')
			{
				$html = \Minify::html($html);
			}
		}

		return $html;
	}

	/**
	 * Redirect
	 *
	 * @param string $location
	 * @param int $code either 301 or null
	 */
	public function redirect ($location, $code = null)
	{
		if ($location instanceof \URL)
		{
			$this->redirect_raw((string) $location, $code);
			return;
		}

		$router = \Router::get_instance();

		// Make sure the host and scheme is present
		$location = \URL::create()
			->scheme($router->url->scheme)
			->host($router->url->host)
			->from_string($location)
			->to_string();

		$this->redirect_raw($location, $code);
	}

	/**
	 * Redirect without manipulating the redirect URL
	 *
	 * @param string $location
	 * @param int $code either 301 or null
	 */
	public function redirect_raw ($location, $code = null)
	{
		if ($code === 301)
		{
			header('HTTP/1.1 301 Moved Permanently');
		}

		header('Location: ' . $location);
	}
}
