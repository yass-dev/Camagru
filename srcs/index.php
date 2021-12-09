<?php

require_once('YassFramework/yass-framework.php');

require_once('entities/user.entity.php');
require_once('entities/publication.entity.php');
require_once('entities/liked-publication.entity.php');
require_once('entities/publication-comment.entity.php');

require_once('repositories/publication.repository.php');

require_once('controllers/test.controller.php');
require_once('controllers/user.controller.php');
require_once('controllers/publication.controller.php');

function initORM()
{
	$orm = new ORM();
	$orm->connect('database', 'camagru', 'root', 'pass');
	$orm->registerEntity(new User);
	$orm->registerEntity(new Publication);
	$orm->registerEntity(new LikedPublication);
	$orm->registerEntity(new PublicationComment);

	$orm->registerRepository(Publication::class, PublicationRepository::class);

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

	$router->addRoute('/', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/publications', [PublicationController::class, 'getAllPublications']);
	$router->addRoute('/publications/:id/likes', [PublicationController::class, 'addLike'], Router::POST_METHOD);
	$router->addRoute('/publications/:id/likes', [PublicationController::class, 'unlike'], Router::DELETE_METHOD);
	$router->addRoute('/publications/:id/comments', [PublicationController::class, 'addComment'], Router::POST_METHOD);
	return $router;
}

session_start();

$orm = initORM();
$router = initRouter();

$GLOBALS['orm'] = $orm;

TemplateEngine::setBaseTemplate('views/base/base.view.php');

$router->execute();

?>