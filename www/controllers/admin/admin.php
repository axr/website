<?php

require_once(ROOT . '/lib/www_controller.php');
require_once(SHARED . '/lib/core/models/cache.php');

class AdminController extends WWWController
{
	public function runCache ()
	{
		if (!User::current()->can('/cache/manage'))
		{
			throw new HTTPException(null, 403);
		}

		if (isset($_POST['_via_post']))
		{
			$mode = isset($_POST['mode']) ? $_POST['mode'] : 'null';

			if ($mode === 'clear_all')
			{
				\Core\Models\Cache::delete_all(array(
					'conditions' => array('1 = 1')
				));

				$this->view->message = 'The cache has been cleared';
			}
			else if ($mode === 'clear_expired')
			{
				\Core\Models\Cache::delete_all(array(
					'conditions' => array('expires < ?', time())
				));

				$this->view->message = 'Expired items have been cleared';
			}
		}

		$this->view->_title = 'Manage cache';
		$this->breadcrumb[] = array(
			'name' => 'Manage cache'
		);

		echo $this->renderView(ROOT . '/views/admin/cache.html');
	}
}

