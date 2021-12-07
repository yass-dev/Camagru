<?php

require_once('YassFramework/yass-framework.php');

require_once('entities/user.entity.php');
require_once('entities/publication.entity.php');
require_once('entities/liked_publication.entity.php');

require_once('controllers/test.controller.php');
require_once('controllers/user.controller.php');
require_once('controllers/gallerie.controller.php');
require_once('controllers/publication.controller.php');

function initORM()
{
	$orm = new ORM();
	$orm->connect('database', 'camagru', 'root', 'pass');
	$orm->registerEntity(new User);
	$orm->registerEntity(new Publication);
	$orm->registerEntity(new LikedPublication);
	$orm->init();
	return $orm;
}

function initRouter()
{	
	$router = new Router();
	$router->addRoute("/test", array('TestController', 'testIndex'));
	$router->addRoute("/account/login", [UserController::class, 'loginView']);
	$router->addRoute("/account/register", [UserController::class, 'registerView']);

	$router->addRoute('/api/account/login', [UserController::class, 'login'], Router::POST_METHOD);
	$router->addRoute('/api/account/register', [UserController::class, 'register']);

	$router->addRoute('/', [GallerieController::class, 'gallerieView']);

	$router->addRoute('/publications/:id/like', [PublicationController::class, 'addLike'], Router::POST_METHOD);
	return $router;
}

session_start();

$orm = initORM();
$router = initRouter();

$GLOBALS['orm'] = $orm;

TemplateEngine::setBaseTemplate('views/base/base.view.php');

$router->execute();

?>