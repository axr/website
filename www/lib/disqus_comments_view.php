<?php

namespace WWW;

class DisqusCommentsView extends \Core\View
{
	/**
	 * __construct
	 */
	public function __construct ($link, $title)
	{
		parent::__construct(ROOT . '/views/disqus_comments.html');

		$this->data['vars'] = array(
			'developer' => \Config::get()->prod ? 'false' : 'true',
			'shortname' => \Config::get()->disqus_shortname,
			'identifier' => $link,
			'title' => str_replace('\'', '\\\'', $title)
		);
	}
}
