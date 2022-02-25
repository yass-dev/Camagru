<?php

class Session
{
	public static function get($key)
	{
		if (isset($_SESSION[$key]))
			return $_SESSION[$key];
		return NULL;
	}

	public static function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}
}

?>