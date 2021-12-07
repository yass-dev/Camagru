<?php

class ForbiddenException extends HttpException
{
	public function __construct($message = "Forbidden")
	{
		parent::__construct(403, $message);
	}
}

?>