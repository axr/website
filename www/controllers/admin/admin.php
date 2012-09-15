<?php

require_once(ROOT . '/lib/www_controller.php');

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
				$query = $this->dbh->prepare('DELETE FROM `www_cache`');
				$query->execute();

				$this->view->message = 'The cache has been cleared';
			}
			else if ($mode === 'clear_expired')
			{
				$query = $this->dbh->prepare('DELETE FROM `www_cache`
					WHERE `www_cache`.`expires` < :now');
				$query->bindValue(':now', time(), PDO::PARAM_STR);
				$query->execute();

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

