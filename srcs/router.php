<?php

require('router/router.class.php');
require('controllers/test.controller.php');

$router = new Router();
$router->addRoute("/users/:id/posts/:post_id", new TestController);

$url = $_SERVER['REQUEST_URI'];

$route = $router->findRoute($url);
$route->render();

?>