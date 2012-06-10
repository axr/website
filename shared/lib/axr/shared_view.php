<?php

class SharedView
{
	/**
	 * Constructor
	 */
	public function __construct ()
	{
		// Resources
		$this->_rsrc_root = Config::get('/shared/rsrc_url');

		// Misc
		$this->_www_url = Config::get('/shared/www_url');
		$this->_search_type = 'mixed';
		$this->_year = date('Y');
	}
}

