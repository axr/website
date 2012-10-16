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

		if ($out->mIsarticle !== true &&
			!in_array($out->mPagetitle, array('Preferences')))
		{
			$out->addStyle('axrbook/css/monobook.css', 'screen');
		}

		$out->addStyle('axrbook/css/axrbook.css', 'screen');
		
		$out->addStyle('axrbook/css/IE50Fixes.css', 'screen', 'lt IE 5.5000');
		$out->addStyle('axrbook/css/IE55Fixes.css', 'screen', 'IE 5.5000');
		$out->addStyle('axrbook/css/IE60Fixes.css', 'screen', 'IE 6');
		$out->addStyle('axrbook/css/IE70Fixes.css', 'screen', 'IE 7');
	}
}

/**
 * @ingroup Skins
 */
class AxrBookTemplate extends BaseTemplate
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

	public function getPersonalTools ()
	{
		$personal_tools = parent::getPersonalTools();

		unset($personal_tools['anonlogin']);
		unset($personal_tools['login']);
		unset($personal_tools['logout']);

		return $personal_tools;
	}
}

