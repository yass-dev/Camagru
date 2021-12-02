<?php

require_once('orm/orm.php');
require_once('models/user.entity.php');

require_once('router/router.class.php');
require_once('controllers/test.controller.php');


function initORM()
{
	$orm = new ORM();
	$orm->connect('database', 'camagru', 'root', 'pass');
	$orm->registerEntity(new User);
	$orm->init();
}

function initRouter()
{	
	$router = new Router();
	$router->addRoute("/", new TestController);
	
	$url = $_SERVER['REQUEST_URI'];
	
	$route = $router->findRoute($url);
	$route->render();
}

initORM();
initRouter();


?>