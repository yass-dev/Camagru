<?php

class BadRequestException extends HttpException
{
	public function __construct($message = "Bad request")
	{
		parent::__construct(400, $message);
	}
}

?>