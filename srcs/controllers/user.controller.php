<?php

require_once('template-engine/template-engine.php');

class UserController
{
	public static function loginView($parameters = [])
	{
		TemplateEngine::render(['views/account/login.view.php']);
	}

	public static function registerView($parameters = [])
	{
		TemplateEngine::render(['views/account/register.view.php']);
	}

	public static function login()
	{
		var_dump($_POST);
	}

	public static function register()
	{
		var_dump($_POST);
	}
}

?>