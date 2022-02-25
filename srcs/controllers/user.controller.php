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
		$password = hash("sha256", md5($_POST['password']));

		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);
		$user = $repository->findOne(['id', 'username', 'email', 'mail_enabled', 'activated'], ['username' => $username, 'password' => $password]);

		if ($user == NULL)
			throw new UnauthorizedException("Bad credentials.");
		if (!boolval($user->activated))
			throw new UnauthorizedException("Your account has not been activated. Please check your mail.");

		Session::set('user_id', $user->id);
		unset($user->password);
		unset($user->activated);
		unset($user->publications);
		echo json_encode($user);
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

		if (!(strlen($username) > 0 && strlen($password) > 4 && strtolower($password) != $password
			&& preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email)))
			throw new BadRequestException();

		if (!ctype_alnum($username))
			throw new BadRequestException("Username can contain only letter and number");

		$user = new User;
		$user->email = $email;
		$user->username = $username;
		$user->password = hash("sha256", md5($password));
		$user->activated = 0;
		$user->mail_enabled = 1;
		$user->activation_id = uniqid(md5("activ_id"));
		$user->restore_password_id = "";
		
		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);

		// Check if username and email are not taken
		if ($repository->findOne(['id'], ['email' => $email]) != NULL)
			throw new ConflictException("This email has been already taken");
		if ($repository->findOne(['id'], ['username' => $username]) != NULL)
			throw new ConflictException("This username has been already taken");

		$user = $repository->insert($user);
		Mailer::sendHtml($email, "Welcom to Camagru", '<a href="' . "http://$_SERVER[HTTP_HOST]/activation?id=$user->activation_id" . '">Active here</a>');

		unset($user->password);
		unset($user->activated);
		unset($user->activation_id);
		unset($user->publications);
		echo json_encode($user);
	}

	public static function updateUser($parameters = [])
	{
		$validator = new DataValidator();
		$_PUT = (array)(json_decode(file_get_contents("php://input")));
		$validator->setArray($_PUT);
		$validator->addConstraint('username', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('username', [Constraint::class, 'IS_NOT_EMPTY']);
		$validator->addConstraint('email', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('email', [Constraint::class, 'IS_NOT_EMPTY']);
		$validator->addConstraint('mail_enabled', [Constraint::class, 'IS_BOOLEAN']);

		if (!$validator->validate())
			throw new BadRequestException($validator->error_message);

		$user_id = $parameters['id'];

		if (!preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $_PUT['email']))
			throw new BadRequestException("Email invalid.");

		if (!ctype_alnum($_PUT['username']))
			throw new BadRequestException("Username can contain only letter and number (" . $_PUT['username'] . ")");

		if ($user_id != Session::get('user_id'))
			throw new UnauthorizedException("You can't alter this user");

		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);

		if ($tmp_user = $repository->findOne(['id'], ['email' => $_PUT['email']]))
			if ($tmp_user == NULL || $tmp_user->id != Session::get('user_id'))
				throw new ConflictException("This email has been already taken");
		if ($tmp_user = $repository->findOne(['id'], ['username' => $_PUT['username']]))
			if ($tmp_user == NULL || $tmp_user->id != Session::get('user_id'))
				throw new ConflictException("This username has been already taken");

		$user = $repository->findOne([], ['id' => $user_id]);
		if ($user)
		{
			$user->username = $_PUT['username'];
			$user->email = $_PUT['email'];
			$user->mail_enabled = (int)$_PUT['mail_enabled'];
			$repository->update($user);
			unset($user->password);
			unset($user->activated);
			unset($user->publications);
			echo json_encode($user);
		}
		else
			throw new NotFoundException("User not found");
	}

	public static function updatePassword($parameters = [])
	{
		$validator = new DataValidator();
		$_PUT = (array)(json_decode(file_get_contents("php://input")));
		$validator->setArray($_PUT);
		$validator->addConstraint('old_password', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('old_password', [Constraint::class, 'IS_NOT_EMPTY']);
		$validator->addConstraint('new_password', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('new_password', [Constraint::class, 'IS_NOT_EMPTY']);

		if (!$validator->validate())
			throw new BadRequestException($validator->error_message);

		$user_id = $parameters['id'];

		if ($user_id != Session::get('user_id'))
			throw new UnauthorizedException("You can't alter this user");

		$password = $_PUT['new_password'];
		if (!(strlen($password) > 4 && strtolower($password) != $password))
			throw new BadRequestException("Password must contain at leat one uppercase letter and have at leat 4 characters");

		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);
		$user = $repository->findOne([], ['id' => $user_id]);
		if ($user)
		{
			if ($user->password != hash("sha256", md5($_PUT['old_password'])))
				throw new UnauthorizedException("Bad old password");

			$user->password = hash("sha256", md5($_PUT['new_password']));
			$repository->update($user);
			unset($user->password);
			unset($user->activated);
			unset($user->publications);
			echo json_encode($user);
		}
	}

	public static function getUserPublications($parameters = [])
	{
		$username = $parameters['username'];
		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(Publication::class);
		$user_repo = $orm->getRepository(User::class);
		$user = $user_repo->findOne(['id'], ['username' => $username]);
		if (!$user)
			throw new NotFoundException("User not found");

		$publications = $repository->findWithRelationsWhere(['user_id' => $user->id]);
		echo json_encode($publications);
	}

	public static function activeUser()
	{
		if (!isset($_GET['id']))
			throw new BadRequestException();
		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);
		$user = $repository->findOne([], ['activation_id' => $_GET['id']]);
		if ($user == NULL)
			throw new NotFoundException();
		$user->activated = 1;
		$repository->update($user);
	}

	public static function logout($parameters)
	{
		session_destroy();
	}

	public static function sendRestoreRequest()
	{
		$validator = new DataValidator();
		$validator->setArray($_POST);
		$validator->addConstraint('username', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('username', [Constraint::class, 'IS_NOT_EMPTY']);
		if (!$validator->validate())
			throw new BadRequestException($validator->error_message);
		
		$username = $_POST['username'];

		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(User::class);
		$user = $repo->findOne([], ['username' => $username]);
		if (!$user)
			throw new NotFoundException("User not found");

		$user->restore_password_id = uniqid('rp_');
		$repo->update($user);

		$mailer = new Mailer();
		$mailer->sendHtml($user->email, "Restore password", '<a href="http://localhost:8080/restore_password/' . $user->restore_password_id . '">Restore here</a>');
		echo "An email has been sent. Check your email to restore your password.";
	}

	public static function restorePassword()
	{
		$validator = new DataValidator();
		$validator->setArray($_POST);
		$validator->addConstraint('new_password', [Constraint::class, 'IS_STRING']);
		$validator->addConstraint('new_password', [Constraint::class, 'IS_NOT_EMPTY']);

		if (!$validator->validate())
			throw new BadRequestException($validator->error_message);

		$password = $_POST['new_password'];
		if (!(strlen($password) > 4 && strtolower($password) != $password))
			throw new BadRequestException("Password must contain at leat one uppercase letter and have at leat 4 characters");

		$restore_id = $_POST['restore_id'];

		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);
		$user = $repository->findOne([], ['restore_password_id' => $restore_id]);
		if ($user)
		{
			$user->password = hash("sha256", md5($password));
			$user->restore_password_id = "";
			$repository->update($user);
			unset($user->password);
			unset($user->activated);
			unset($user->publications);
			echo "Password successfully modified.";
		}
		else
			throw new NotFoundException("User not found");
	}

	public static function checkAuth()
	{
		if (!Session::get('user_id'))
			throw new UnauthorizedException("You are not logged in.");
	
		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(User::class);
		$user = $repository->findOne([], ['id' => Session::get('user_id')]);

		if (!$user)
			throw new NotFoundException("User not found");
		unset($user->password);
		unset($user->activated);
		unset($user->publications);
		echo json_encode($user);
	}
}

?>