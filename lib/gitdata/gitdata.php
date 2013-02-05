<?php

namespace GitData;

define('GITDATA_ROOT', dirname(__FILE__));

class GitData
{
	public static $root = null;
}

require_once(GITDATA_ROOT . '/file.php');
require_once(GITDATA_ROOT . '/model.php');
require_once(GITDATA_ROOT . '/exceptions/entity_invalid.php');
require_once(GITDATA_ROOT . '/models/page.php');
