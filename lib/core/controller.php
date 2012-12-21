<?php

namespace Core;

require_once(SHARED . '/lib/core/rsrc.php');
require_once(SHARED . '/lib/core/minify.php');
require_once(SHARED . '/lib/core/router.php');
require_once(SHARED . '/lib/core/url.php');
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
	 * Render a view
	 *
	 * @param string $view_path
	 * @return string
	 */
	protected function renderView ($view_path)
	{
		$layout_path = SHARED . '/views/layout.html';

		$this->view->{'g/content'} = $this->render_simple_view($view_path, $this->view);
		$html = $this->render_simple_view($layout_path, $this->view, array(
			'minify' => true,
			'fallback_template' => '{{{g/content}}}'
		));

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
	protected function render_simple_view ($path, \StdClass $view, array $options = array())
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
