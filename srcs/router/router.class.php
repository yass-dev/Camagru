<?php

require_once('router/route.class.php');
require_once('controllers/AbstractController.php');

class Router
{
	private $routes;
	
	function __construct()
	{
		$this->routes = array();
	}

	/**
	 * @param string $path 
	 * @param \AbstractController $controller
	 * @var \Route $new_route
	 */
	public function addRoute($path, $controller)
	{
		$new_route = $this->initRoute($path, $controller);
		array_push($this->routes, $new_route);
	}

	/**
	 * @var \Route $new_route
	 */
	private function initRoute($path, $controller)
	{
		$new_route = new Route;
		$new_route->path = $path;
		$new_route->controller = $controller;
	
		$path_parts = explode('/', $new_route->path);
	}
}

?>