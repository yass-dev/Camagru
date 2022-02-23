<?php

class Constraint
{
	public static function IS_INT($x)
	{
		$type = gettype($x);
		return ($type == 'integer' || $type == 'double' || is_numeric($x));
	}

	public static function IS_STRING($x)
	{
		$type = gettype($x);
		return ($type == 'string');
	}

	public static function IS_BOOLEAN($x)
	{
		$type = gettype($x);
		return ($type == 'boolean');
	}

	public static function IS_NOT_NULL($x)
	{
		$type = gettype($x);
		return ($type == 'NULL');
	}

	public static function IS_STRICTLY_POSITIVE($x)
	{
		return (intval($x) > 0);
	}

	public static function IS_POSITIVE($x)
	{
		return (intval($x) >= 0);
	}

	public static function IS_NEGATIVE($x)
	{
		return ($x < 0);
	}

	public static function IS_NOT_EMPTY($x)
	{
		return (strlen($x) > 0);
	}
}

?>