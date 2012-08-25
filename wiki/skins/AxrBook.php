<?php
/**
 * AxrBook
 *
 * @ingroup Skins
 */

if(!defined('MEDIAWIKI'))
{
	die('NO!');
}

require_once('MonoBook.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 */
class SkinAxrBook extends SkinTemplate
{
	public $skinname = 'axrbook';
	public $stylename = 'axrbook';
	public $template = 'AxrBookTemplate';
	public $useHeadElement = true;

	function setupSkinUserCss (OutputPage $out)
	{
		parent::setupSkinUserCss($out);

		$out->addStyle('axrbook/monobook.css', 'screen');
		$out->addStyle('axrbook/axrbook.css', 'screen');
		
		$out->addStyle('monobook/IE50Fixes.css', 'screen', 'lt IE 5.5000');
		$out->addStyle('monobook/IE55Fixes.css', 'screen', 'IE 5.5000');
		$out->addStyle('monobook/IE60Fixes.css', 'screen', 'IE 6');
		$out->addStyle('monobook/IE70Fixes.css', 'screen', 'IE 7');
	}
}

/**
 * @todo document
 * @ingroup Skins
 */
class AxrBookTemplate extends MonoBookTemplate
{

	/**
	 * @var Skin
	 */
	public $skin;

	/**
	 * Template filter callback for MonoBook skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	public function execute ()
	{
		global $IP, $wgUser;

		$this->skin = $this->data['skin'];

		$out = RequestContext::getMain()->getOutput();

		// MW wants it here
		wfSuppressWarnings();

		require_once(SHARED . '/lib/mustache.php/Mustache.php');
		require_once(SHARED . '/lib/core/rsrc.php');
		require_once(SHARED . '/lib/core/minify.php');

		$mustache = new Mustache();
		$rsrc = new RSRC();
		$view = new StdClass();

		// Load styles
		$rsrc->loadBundle('css/bundle_shared.css');

		// Load scripts
		$rsrc->loadScript('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
		$rsrc->loadBundle('js/bundle_shared.js');

		// Basic stuff
		$view->_title = $this->data['title'];
		$view->_content = $this->content();	

		// URLs
		$view->_rsrc_root = Config::get('/shared/rsrc_url');
		$view->_www_url = Config::get('/shared/www_url');
		$view->_wiki_url = Config::get('/shared/wiki_url');
		
		// Resources
		$view->_rsrc_styles = $out->buildCssLinks($this->skin) .
			$rsrc->getStylesHTML();
		$view->_rsrc_scripts = $rsrc->getScriptsHTML();

		// Misc
		$view->_year = date('Y');

		// Additional HTML
		$view->_html_head = $out->getHeadLinks($this->skin, true) .
			$out->getHeadScripts($this->skin);
			$out->getHeadItems();
		$view->_html_bottom = $this->getTrail();

		if (!is_object($wgUser) || $wgUser->getID() == 0)
		{
			$view->_user = false;

			$view->_url_login = Config::get('/shared/wiki_url') .
				'/Special:UserLogin';	
		}
		else
		{
			$view->_user = new StdClass();

			$view->_url_profile = Config::get('/shared/wiki_url') .
				'/User:' . $wgUser->getName();
			$view->_url_account = Config::get('/shared/wiki_url') .
				'/Special:Preferences';
			$view->_url_logout = Config::get('/shared/wiki_url') .
				'/Special:UserLogout';
		}

		$view->_breadcrumb = array(
			array(
				'name' => 'Home',
				'link' => Config::get('/shared/www_url')
			),
			array(
				'name' => 'Wiki',
				'link' => Config::get('/shared/wiki_url')
			),
			array(
				'name' => $this->data['title'],
				'current' => true
			)
		);

		$html = $mustache->render(
			file_get_contents(SHARED . '/views/layout.html'), $view);

		echo Minify::html($html);

		wfRestoreWarnings();
	}

	public function getTrail ()
	{
		ob_start();
		$this->printTrail();
		$trail = ob_get_contents();
		ob_end_clean();

		return $trail;
	}

	public function content ()
	{
		ob_start();
		include('axrbook/content.tpl.php');
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * This function exists only to hide the default search box as we
	 * use our own search box.
	 */
	public function searchBox ()
	{
	}

	public function getPersonalTools ()
	{
		$personal_tools = parent::getPersonalTools();

		unset($personal_tools['anonlogin']);
		unset($personal_tools['login']);
		unset($personal_tools['logout']);

		return $personal_tools;
	}

	public function getLinkLogin ()
	{
		$personal_tools = parent::getPersonalTools();
		return $personal_tools['anonlogin']['links'][0]['href'];
	}

	public function getLinkLogout ()
	{
		$personal_tools = parent::getPersonalTools();
		return $personal_tools['logout']['links'][0]['href'];
	}
}

