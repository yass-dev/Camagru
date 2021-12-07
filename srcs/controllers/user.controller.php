<?php

require_once('YassFramework/template-engine/template-engine.php');

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
		$validator = new DataValidator();
		$validator->setArray($_POST);
		$validator->addConstraint('username', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('username', [Constraint::class, 'IS_NOT_EMPTY']);
		$validator->addConstraint('password', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('password', [Constraint::class, 'IS_NOT_EMPTY']);

		if (!$validator->validate())
			throw new BadRequestException();

		$username = $_POST['username'];
		$password = $_POST['password'];

		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);
		$user = $repository->findOne(['id'], ['username' => $username, 'password' => $password]);

		if ($user == NULL)
			throw new UnauthorizedException();

		Session::set('user_id', $user->id);
	}

	public static function register()
	{
		$email = isset($_POST['email']) ? $_POST['email'] : NULL;
		$username = isset($_POST['username']) ? $_POST['username'] : NULL;
		$password = isset($_POST['password']) ? $_POST['password'] : NULL;

		if ($email === NULL)
			throw new Exception("Error: email cannot be null");
		else if ($username === NULL)
			throw new Exception("Error: username cannot be null");
		else if ($password === NULL)
			throw new Exception("Error: password cannot be null");

		$user = new User;
		$user->email = $email;
		$user->username = $username;
		$user->password = $password;
		$user->activated = 0;
		
		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);

		// Check if username and email are not taken

		echo json_encode($repository->insert($user));
	}
}

?>