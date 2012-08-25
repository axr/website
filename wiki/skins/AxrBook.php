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

global $IP;

require_once('MonoBook.php');

require_once(SHARED . '/lib/core/http_exception.php');
require_once(SHARED . '/lib/core/rsrc.php');
require_once($IP . '/skins/axrbook/controller.php');

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

		// MW wants it here
		wfSuppressWarnings();

		try
		{
			$controller = new AxrBookController();

			$controller->mwt = $this;
			$controller->wgUser = $wgUser;

			$controller->initialize();
			$controller->run();
		}
		catch (HTTPException $e)
		{
			echo $e->getCode() . ': ' . $e->getMessage();
		}

		wfRestoreWarnings();
	}

	public function content ()
	{
		ob_start();
		include(ROOT . '/skins/axrbook/content.tpl.php');
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
}

