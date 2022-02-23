<?php

require_once('YassFramework/router/route.class.php');

class Router
{
	const GET_METHOD = "GET";
	const POST_METHOD = "POST";
	const DELETE_METHOD = "DELETE";
	const PATCH_METHOD = "PATCH";
	const PUT_METHOD = "PUT";

	private $routes;
	
	function __construct()
	{
		$this->routes = array();
	}

	/**
	 * @param string $path
	 * @param callable $func
	 */
	public function addRoute($path, callable $func, $method = Router::GET_METHOD)
	{
		$new_route = new Route($path, $func, $method);
		array_push($this->routes, $new_route);
	}

	/**
	 * @param string $url
	 * @var \Route $route
	 * @return \Route
	 */
	private function findRoute($url)
	{
		$url = explode('?', $url)[0];		// Remove query string
		$url_parts = explode('/', $url);
		foreach ($this->routes as $route)
		{
			// Clone to bind parameters value in the route without modifying the original
			$route = clone $route;
			$route_parts = explode('/', $route->path);
			
			// If they have different numbers of part (/test/abc => 2 parts)
			if (count($route_parts) != count($url_parts))
				continue ;
			
			if ($_SERVER['REQUEST_METHOD'] != $route->method)
				continue ;

			$match = true;
			for ($i = 0; $i < count($url_parts); $i++)
			{
				$origin_part = $url_parts[$i];
				$tmp_part = $route_parts[$i];

				// If the part of the url is a parameter
				if (str_starts_with($tmp_part, ':'))
				{
					if (empty($origin_part))
						$match = false;
					else
						$route->setParameter(substr($tmp_part, 1), $origin_part);
				}
				// Else if the part is not a parameter and the 2 parts are different
				else if ($origin_part != $tmp_part)
				{
					$match = false;
					break ;
				}
			}

			if ($match)
				return $route;
		}
		return NULL;
	}

	public function execute()
	{
		$url = $_SERVER['REQUEST_URI'];
		$route = $this->findRoute($url);
		try
		{
			if ($route == NULL)
				throw new NotFoundException();
			$route->execute();
		}
		catch (HttpException $e)
		{
			http_response_code($e->getHttpCode());
			echo $e->getErrorMessage();
		}
	}
}

?>