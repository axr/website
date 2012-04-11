<?php
/**
 * AxrBook
 *
 * @ingroup Skins
 */

if(!defined('MEDIAWIKI')) {
	die('NO!');
}

require_once('MonoBook.php');

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 */
class SkinAxrBook extends SkinMonoBook {
	public $skinname = 'axrbook';
	public $stylename = 'axrbook';
	public $template = 'AxrBookTemplate';
	public $useHeadElement = true;

	function setupSkinUserCss(OutputPage $out) {
		parent::setupSkinUserCss($out);

		$out->addStyle('/sites/default/themes/axr/css/style.css', 'screen');
		$out->addStyle('axrbook/axrbook.css', 'screen');	
	}
}

/**
 * @todo document
 * @ingroup Skins
 */
class AxrBookTemplate extends MonoBookTemplate {

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
	public function execute() {
		global $wgUser;

		$this->skin = $this->data['skin'];

		// Suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();

		$this->html('headelement');

		include('axrbook/main.tpl.php');

		$this->printTrail();
		echo Html::closeElement('body');
		echo Html::closeElement('html');
		wfRestoreWarnings();
	}

	public function content() {
		include('axrbook/content.tpl.php');
	}

	/**
	 * This function exists only to hide the default search box as we
	 * use our own search box.
	 */
	public function searchBox() {
	}

	public function getPersonalTools() {
		$personal_tools = parent::getPersonalTools();

		unset($personal_tools['anonlogin']);
		unset($personal_tools['login']);
		unset($personal_tools['logout']);

		return $personal_tools;
	}

	public function getLinkLogin() {
		$personal_tools = parent::getPersonalTools();
		return $personal_tools['anonlogin']['links'][0]['href'];
	}

	public function getLinkLogout() {
		$personal_tools = parent::getPersonalTools();
		return $personal_tools['logout']['links'][0]['href'];
	}
}

