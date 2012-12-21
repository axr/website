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

		// Styles/scripts/metainfo for head
		$this->view->{'g/html_head'} = $out->getHeadLinks($this->mwt->skin, true) .
			$out->getHeadScripts($this->mwt->skin) .
			$out->buildCssLinks($this->mwt->skin) .
			$this->rsrc->getStylesHTML() .
			$out->getHeadItems();

		// Scripts for footer
		$this->view->{'g/html_bottom'} = $this->getMWTrail() .
			$this->rsrc->getScriptsHTML();

		$usermenu = null;
		$search = null;

		// Create the user menu
		if (!is_object($this->wgUser) || $this->wgUser->getID() == 0)
		{
			$usermenu = $this->render_simple_view(SHARED . '/views/layout_usermenu.html',
				(object) array(
				'can_login' => true,
				'login_url' => Config::get('/shared/wiki_url')
					->copy()
					->path('/Special:MixedLogin'),
				'login_label' => 'Login to wiki'
			));
		}
		else
		{
			$usermenu = $this->render_simple_view(SHARED . '/views/layout_usermenu.html',
				(object) array(
				'show_usermenu' => true,
				'user' => array(
					'id' => $this->wgUser->getID(),
					'name' => $this->wgUser->getName(),
					'url' => Config::get('/shared/wiki_url')
						->copy()
						->path('/User:' . $this->wgUser->getName())
				),
				'links' => array(
					array(
						'text' => 'My Talk',
						'href' => Config::get('/shared/wiki_url')
							->copy()
							->path('/User_talk:' . $this->wgUser->getName())
					),
					array(
						'text' => 'My preferences',
						'href' => Config::get('/shared/wiki_url')
							->copy()
							->path('/Special:Preferences')
					),
					array(
						'text' => 'My watchlist',
						'href' => Config::get('/shared/wiki_url')
							->copy()
							->path('/Special:Watchlist')
					),
					array(
						'text' => 'My contributions',
						'href' => Config::get('/shared/wiki_url')
							->copy()
							->path('/Special:Contributions/' . $this->wgUser->getName())
					),
					array(
						'text' => 'Log out',
						'href' => Config::get('/shared/wiki_url')
							->copy()
							->path('/Special:UserLogout')
					)
				)
			));
		}

		// Create the search box
		// $search = $this->render_simple_view(SHARED . '/views/layout_search.html', (object) array(
		// 	'placeholder' => 'Search the wiki'
		// ));

		$this->view->{'g/html_secondary'} = $usermenu . $search;

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
			->from_string('/api.php?action=autoauth&format=json');
	}

	/**
	 * Run the controller
	 */
	public function run ()
	{
		$out = RequestContext::getMain()->getOutput();

		$this->view->{'_title'} = $this->mwt->data['title'];
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
