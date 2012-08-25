<?php

require_once(SHARED . '/lib/core/controller.php');
require_once(SHARED . '/lib/core/minify.php');

class AxrBookController extends Controller
{
	/**
	 * Mediawiki template class
	 *
	 * @var AxrBookTemplate
	 */
	public $mwt = null;

	/**
	 * $wgUser
	 */
	public $wgUser = null;

	/**
	 * Initialize
	 */
	public function initialize ()
	{
		$out = RequestContext::getMain()->getOutput();

		// Load styles
		$this->rsrc->loadBundle('css/bundle_shared.css');

		// Load scripts
		$this->rsrc->loadScript('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
		$this->rsrc->loadBundle('js/bundle_shared.js');
		$this->rsrc->loadBundle('js/bundle_rainbow.js');

		// Resources
		$this->view->_rsrc_styles = $out->buildCssLinks($this->mwt->skin) .
			$this->rsrc->getStylesHTML();
		$this->view->_rsrc_scripts = $this->rsrc->getScriptsHTML();

		// Additional MW HTML
		$this->view->_html_head = $out->getHeadLinks($this->mwt->skin, true) .
			$out->getHeadScripts($this->mwt->skin);
			$out->getHeadItems();
		$this->view->_html_bottom = $this->getMWTrail();

		// Local URL prefix
		$wwwroot = Config::get('/shared/wiki_url');

		// Variables for the user menu
		if (!is_object($this->wgUser) || $this->wgUser->getID() == 0)
		{
			$this->view->_user = false;
			$this->view->_url_login = $wwroot . '/Special:UserLogin';	
		}
		else
		{
			$this->view->_user = new StdClass();

			$this->view->_url_profile = $wwwroot . '/User:' . $this->wgUser->getName();
			$this->view->_url_account = $wwwroot . '/Special:Preferences';
			$this->view->_url_logout = $wwwroot . '/Special:UserLogout';
		}
	}

	/**
	 * Run the controller
	 */
	public function run ()
	{
		$this->view->_title = $this->mwt->data['title'];
		$this->breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => Config::get('/shared/www_url')
			),
			array(
				'name' => 'Wiki',
				'link' => Config::get('/shared/wiki_url')
			),
			array(
				'name' => $this->mwt->data['title']
			)
		);

		$this->view->_mw_content = $this->mwt->content();

		$html = $this->renderView(ROOT . '/skins/axrbook/layout.html');
		echo Minify::html($html);
	}

	/**
	 * Get MW trailing HTML
	 *
	 * @return string
	 */
	public function getMWTrail ()
	{
		ob_start();
		$this->mwt->printTrail();
		$trail = ob_get_contents();
		ob_end_clean();

		return $trail;
	}
}

