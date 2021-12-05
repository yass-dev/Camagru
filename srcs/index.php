<?php

require_once('orm/orm.php');
require_once('models/user.entity.php');

require_once('router/router.class.php');
require_once('controllers/test.controller.php');
require_once('controllers/user.controller.php');

require_once('template-engine/template-engine.php');

function initORM()
{
	$orm = new ORM();
	$orm->connect('database', 'camagru', 'root', 'pass');
	$orm->registerEntity(new User);
	// $orm->init();
	return $orm;
}

function initRouter()
{	
	$router = new Router();
	$router->addRoute("/test", array('TestController', 'testIndex'));
	$router->addRoute("/account/login", [UserController::class, 'loginView']);
	$router->addRoute("/account/register", [UserController::class, 'registerView']);

	$router->addRoute('/api/account/login', [UserController::class, 'login']);
	$router->addRoute('/api/account/register', [UserController::class, 'register']);
	return $router;
}

$orm = initORM();
$router = initRouter();

TemplateEngine::setBaseTemplate('views/base/base.view.php');

$router->execute();

?>