<?php

namespace GitData;

abstract class Model
{
	/**
	 * __isset
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset ($key)
	{
		return isset($this->info->$key) ||
			method_exists(get_called_class(), 'get_' . $key);
	}

	/**
	 * __get
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		if (isset($this->info->$key))
		{
			return $this->info->$key;
		}

		if (method_exists(get_called_class(), 'get_' . $key))
		{
			return $this->{'get_' . $key}();
		}

		return null;
	}
}
