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
			$this->view->_url_login = $wwroot . '/Special:OpenIDLogin';
			$this->view->_label_login = 'Login to wiki';
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

		// Another way to display personal tools
		/**$personal_tools = $this->mwt->getPersonalTools();

		foreach ($personal_tools as $key => $item)
		{
			$this->tabs[] = array(
				'name' => $item['links'][0]['text'],
				'link' => $item['links'][0]['href'],
				'current' => $item['active']
			);
		}**/

		$footer_links = $this->mwt->getFooterLinks('flat');

		$this->view->footer_has_links = count($footer_links) > 0;
		$this->view->footer_infolinks = array();
		$this->view->footer_links = array();

		foreach ($footer_links as $link)
		{
			if (in_array($link, array('lastmod', 'viewcount')))
			{
				$this->view->footer_infolinks[] = $this->getMWhtml($link);
			}
			else
			{
				$this->view->footer_links[] = $this->getMWhtml($link);
			}
		}

		$this->view->mwt = $this->mwt;
		$this->view->html = array(
			'userlangattributes' => $this->getMWhtml('userlangattributes'),
			'subtitle' => $this->getMWhtml('subtitle'),
			'undelete' => $this->getMWhtml('undelete'),
			'newtalk' => $this->getMWhtml('newtalk'),
			'bodytext' => $this->getMWhtml('bodytext'),
			'catlinks' => $this->getMWhtml('catlinks'),
			'dataAfterContent' => $this->getMWhtml('dataAfterContent')
		);
		$this->view->msg = array(
			'tagline' => $this->getMWmsg('tagline'),
			'jumpto' => $this->getMWmsg('jumpto'),
			'jumptonavigation' => $this->getMWmsg('jumptonavigation'),
			'jumptosearch' => $this->getMWmsg('jumptosearch'),
			'personaltools' => $this->getMWmsg('personaltools'),
			'views' => $this->getMWmsg('views')
		);

		$this->view->cactions =
			$this->makeListItemArray($this->mwt->data['content_actions']);
		$this->view->personal_tools =
			$this->makeListItemArray($this->mwt->getPersonalTools());
		$this->view->portals = array();

		foreach ($this->mwt->data['sidebar'] as $box => $content)
		{
			if ($content === false ||
				in_array($box, array('SEARCH', 'LANGUAGES')))
			{
				continue;
			}

			if ($box === 'TOOLBOX')
			{
				$this->view->portals[] = $this->renderPortalToolbox();
			}
			else
			{
				$this->view->portals[] = $this->renderPortalBox($box, $content);
			}
		}

		$html = $this->renderView(ROOT . '/skins/axrbook/layout.html');
		echo Minify::html($html);
	}

	public function renderPortalBox ($box, $content)
	{
		$mustache = new Mustache();
		$template = file_get_contents(ROOT . '/skins/axrbook/portal_box.html');

		$view = new StdClass();
		$view->attrs = ' class="generated-sidebar portlet" id="' .
			Sanitizer::escapeId('p-' . $box) . '"';
		$view->msg_title = wfMessage($box)->exists() ?
			$this->getMWmsg($box) : $box;

		if (is_array($content))
		{
			$view->content = $this->makeListItemArray($content);
		}
		else
		{
			$view->content = array($content);
			$view->{'content_raw?'} = true;
		}

		return $mustache->render($template, $view);
	}

	public function renderPortalToolbox ()
	{
		$that = $this;

		$mustache = new Mustache();
		$template = file_get_contents(ROOT . '/skins/axrbook/portal_toolbox.html');

		$view = new StdClass();
		$view->msg_title = $this->getMWmsg('toolbox');
		$view->toolbox = $this->makeListItemArray($that->mwt->getToolbox());
		$view->hook_toolbox_end = function () use ($that)
		{
			wfRunHooks('MonoBookTemplateToolboxEnd', array(&$that->mwt));
			wfRunHooks('SkinTemplateToolboxEnd', array(&$that->mwt, true));
		};

		return $mustache->render($template, $view);
	}

	public function makeListItemArray($list)
	{
		$array = array();

		foreach ($list as $key => $item)
		{
			$array[] = $this->mwt->makeListItem($key, $item);
		}

		return $array;
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

	/**
	 * Wrapper for $this->mwt->html
	 *
	 * @param string $name
	 * @return string
	 */
	public function getMWhtml ($name)
	{
		ob_start();
		$this->mwt->html($name);
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Wrapper for $this->mwt->msg
	 *
	 * @param string $name
	 * @return string
	 */
	public function getMWmsg ($name)
	{
		ob_start();
		$this->mwt->msg($name);
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}
}

