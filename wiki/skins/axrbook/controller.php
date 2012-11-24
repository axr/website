<?php

require_once(SHARED . '/lib/axr/controller.php');
require_once(SHARED . '/lib/core/minify.php');
require_once(SHARED . '/lib/mustache/src/mustache.php');

class AxrBookController extends \AXR\Controller
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
		global $wgSiteNotice;

		$out = RequestContext::getMain()->getOutput();

		// Load styles
		$this->rsrc->loadBundle('css/bundle_shared.css');
		$this->rsrc->loadBundle('css/bundle_wiki.css');

		// Load scripts
		$this->rsrc->loadScript('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
		$this->rsrc->loadBundle('js/bundle_shared.js');
		$this->rsrc->loadBundle('js/bundle_wiki.js');
		$this->rsrc->loadBundle('js/bundle_rainbow.js');

		// Resources
		$this->view->{'g/rsrc/styles'} = $out->buildCssLinks($this->mwt->skin) .
			$this->rsrc->getStylesHTML();
		$this->view->{'g/rsrc/scripts'} = $this->rsrc->getScriptsHTML();

		// Additional MW HTML
		$this->view->{'g/html_head'} = $out->getHeadLinks($this->mwt->skin, true) .
			$out->getHeadScripts($this->mwt->skin);
			$out->getHeadItems();
		$this->view->{'g/html_bottom'} = $this->getMWTrail(). <<<DATA
<script>
	withApp(function ()
	{
		App.Rsrc.file('js/app_auth.js').use(function ()
		{
			App.Auth.initialize();
			App.Auth.autoauth();
		});
	});
</script>
DATA;

		// Local URL prefix
		$wwwroot = Config::get('/shared/wiki_url');

		// Variables for the user menu
		if (!is_object($this->wgUser) || $this->wgUser->getID() == 0)
		{
			$this->view->{'g/login_show'} = true;
			$this->view->{'g/url_login'} = $wwroot . '/Special:MixedLogin';
			$this->view->{'g/url_login/label'} = 'Login to wiki';
		}
		else
		{
			$this->view->{'g/user'} = new StdClass();
			$this->view->{'g/user'}->name = $this->wgUser->getName();
			$this->view->{'g/user'}->url = $wwwroot . '/User:' . $this->wgUser->getName();

			$this->view->{'g/user'}->links = array(
				array(
					'href' => $wwwroot . '/User_talk:' . $this->wgUser->getName(),
					'text' => 'My Talk'
				),
				array(
					'href' => $wwwroot . '/Special:Preferences',
					'text' => 'My preferences'
				),
				array(
					'href' => $wwwroot . '/Special:Watchlist',
					'text' => 'My watchlist'
				),
				array(
					'href' => $wwwroot . '/Special:Contributions/' . $this->wgUser->getName(),
					'text' => 'My contributions'
				),
				array(
					'href' => $wwwroot . '/Special:UserLogout',
					'text' => 'Log out'
				)
			);
		}

		$this->view->site_notice = isset($wgSiteNotice) ? $wgSiteNotice : null;
		$this->view->footer_info = array();
		$this->view->footer_links = array();

		$footer_links = $this->mwt->getFooterLinks('flat');

		foreach ($footer_links as $link)
		{
			if (in_array($link, array('lastmod', 'viewcount')))
			{
				$this->view->footer_info[] = $this->getMWhtml($link);
			}
			else
			{
				$this->view->footer_links[] = $this->getMWhtml($link);
			}
		}

		$this->view->{'g/app_vars'}->session->is_logged =
			is_object($this->wgUser) && $this->wgUser->getID() !== 0;

		$this->view->{'g/app_vars'}->site->url = (string) \Config::get('/shared/wiki_url');
		$this->view->{'g/app_vars'}->site->app_id = 'wiki';
		$this->view->{'g/app_vars'}->site->aa_handler = (string) \Config::get('/shared/wiki_url')
			->copy()
			->path('/Special:Autoauth');
	}

	/**
	 * Run the controller
	 */
	public function run ()
	{
		$out = RequestContext::getMain()->getOutput();

		$this->view->{'g/title'} = $this->mwt->data['title'];
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

		foreach ($this->mwt->data['content_actions'] as $action)
		{
			$this->tabs[] = array(
				'link' => $action['href'],
				'name' => $action['text'],
				'class' => $action['id'] . ' ' . $action['class'],
				'current' => strpos($action['class'], 'selected') !== false
			);
		}

		$this->view->user_links = $this->mwt->getPersonalTools();

		$this->view->is_article = $out->mIsArticleRelated;
		$this->view->bodytext = $this->getMWhtml('bodytext');
		$this->view->category_links = isset($out->mCategoryLinks['normal']) ?
			$out->mCategoryLinks['normal'] : array();

		$this->view->after_content = $this->getMWhtml('dataAfterContent');

		$html = $this->renderView(ROOT . '/skins/axrbook/layout.html');
		echo Minify::html($html);
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

