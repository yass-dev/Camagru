<?php

class ConflictException extends HttpException
{
	public function __construct($message = "Conflict")
	{
		parent::__construct(409, $message);
	}
}

?>