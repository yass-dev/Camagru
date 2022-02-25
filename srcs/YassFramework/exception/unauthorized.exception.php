<?php

class UnauthorizedException extends HttpException
{
	public function __construct($message = "Unauthorized")
	{
		parent::__construct(401, $message);
	}
}

?>