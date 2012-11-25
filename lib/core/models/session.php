<?php

namespace Core\Models;

class Session extends \ActiveRecord\Model
{
	static $table_name = 'www_sessions';

	static $before_save = array('encode_data');
	static $after_construct = array('decode_data');

	/**
	 * Encode session data
	 */
	public function encode_data ()
	{
		$this->data = json_encode($this->data);
	}

	/**
	 * Decode session data
	 */
	public function decode_data ()
	{
		$this->data = json_decode($this->data);
	}

	/**
	 * Returns whether the session is valid or not
	 *
	 * @return bool
	 */
	public function is_valid ()
	{
		return $this->expires > time();
	}
}
