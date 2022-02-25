<?php

class NotFoundException extends HttpException
{
	public function __construct($message = "Not found")
	{
		parent::__construct(404, $message);
	}
}

?>