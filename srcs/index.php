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
	$GLOBALS['orm'] = $orm;
	return $orm;
}

function initRouter()
{	
	$router = new Router();

	$router->addRoute('/api/account/login', [UserController::class, 'login'], Router::POST_METHOD);
	$router->addRoute('/api/account/register', [UserController::class, 'register'], Router::POST_METHOD);
	$router->addRoute('/api/account/logout', [UserController::class, 'logout'], Router::POST_METHOD);
	$router->addRoute('/api/account/send_restore_request', [UserController::class, 'sendRestoreRequest'], Router::POST_METHOD);
	$router->addRoute('/api/account/restore_password', [UserController::class, 'restorePassword'], Router::POST_METHOD);
	$router->addRoute('/api/account/check_auth', [UserController::class, 'checkAuth'], Router::GET_METHOD);

	$router->addRoute('/api/users/:id', [UserController::class, 'updateUser'], Router::PUT_METHOD);
	$router->addRoute('/api/users/:id/password', [UserController::class, 'updatePassword'], Router::PUT_METHOD);
	$router->addRoute('/api/users/:username/publications', [UserController::class, 'getUserPublications']);
	$router->addRoute('/activation', [UserController::class, 'activeUser']);

	$router->addRoute('/publications', [PublicationController::class, 'getAllPublications']);
	$router->addRoute('/publications/:id/likes', [PublicationController::class, 'addLike'], Router::POST_METHOD);
	$router->addRoute('/publications/:id/likes', [PublicationController::class, 'unlike'], Router::DELETE_METHOD);
	$router->addRoute('/publications/:id/comments', [PublicationController::class, 'addComment'], Router::POST_METHOD);
	$router->addRoute('/api/publications/:id', [PublicationController::class, 'getPublication'], Router::GET_METHOD);
	$router->addRoute('/api/publications/:id', [PublicationController::class, 'removePublication'], Router::DELETE_METHOD);

	$router->addRoute('/api/users/:id/publications', [PublicationController::class, 'createNew'], Router::POST_METHOD);

	$router->addRoute('/', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/login', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/register', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/builder', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/forgotten_password', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/restore_password/:restore_id', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/users/:username', [PublicationController::class, 'gallerieView']);
	$router->addRoute('/publications/:id', [PublicationController::class, 'gallerieView']);

	return $router;
}

session_start();

TemplateEngine::setBaseTemplate('views/base/base.view.php');
Mailer::setFrom("yel-alou@camagru.com");

$orm = initORM();
$router = initRouter();
$router->execute();

?>