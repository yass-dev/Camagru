<?php

require_once('router/route.class.php');

class Router
{
	private $routes;
	
	function __construct()
	{
		$this->routes = array();
	}

	/**
	 * 
	 */
	public function addRoute($path, callable $func)
	{
		$new_route = new Route($path, $func);
		array_push($this->routes, $new_route);
	}

	/**
	 * @param string $url
	 * @var \Route $route
	 * @return \Route
	 */
	private function findRoute($url)
	{
		$url_parts = explode('/', $url);
		foreach ($this->routes as $route)
		{
			// Clone to bind parameters value in the route without modifying the original
			$route = clone $route;
			$route_parts = explode('/', $route->path);
			
			// If they have different numbers of part (/test/abc => 2 parts)
			if (count($route_parts) != count($url_parts))
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
		return null;
	}

	public function execute()
	{
		$url = $_SERVER['REQUEST_URI'];
		$route = $this->findRoute($url);
		if ($route != null)
			$route->execute();
		else	// 404
		{
			echo "404 route not found";
		}
	}
}

?>