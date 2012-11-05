<?php

namespace Core;

class Model extends \ActiveRecord\Model
{
	/**
	 * __isset
	 *
	 * @param string $attribute_name
	 * @return bool
	 */
	public function __isset ($attribute_name)
	{
		if (method_exists(get_called_class(), 'get_' . $attribute_name))
		{
			return true;
		}

		return parent::__isset($attribute_name);
	}
}
