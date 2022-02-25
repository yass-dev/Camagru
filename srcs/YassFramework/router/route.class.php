<?php

class Route
{
	public $path = "";
	public $controller_func;
	public $parameters = array();
	public $method;

	/**
	 * @param string $path
	 * @var \Route $new_route
	 */
	public function __construct($path, $controller_func, $method)
	{
		$this->path = $path;
		$this->controller_func = $controller_func;
		$this->method = $method;
	
		$path_parts = explode('/', $this->path);
		foreach ($path_parts as $part)
		{
			if (str_starts_with($part, ':'))
			{
				$param_name =  substr($part, 1);
				$this->parameters[$param_name] = "";
			}
		}
	}

	/**
	 * @param string $name
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	public function execute()
	{
		($this->controller_func)($this->parameters);
	}
}

?>