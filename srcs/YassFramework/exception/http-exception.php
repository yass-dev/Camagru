<?php

require_once('bad-request.exception.php');
require_once('not-found.exception.php');
require_once('unauthorized.exception.php');
require_once('forbidden.exception.php');
require_once('conflict.exception.php');

abstract class HttpException extends Exception
{
	private $http_code;
	private $error_message;

	public function __construct($code, $message)
	{
		$this->http_code = $code;
		$this->error_message = $message;
	}

	public function getHttpCode()
	{
		return $this->http_code;
	}

	public function getErrorMessage()
	{
		return $this->error_message;
	}
}

?>