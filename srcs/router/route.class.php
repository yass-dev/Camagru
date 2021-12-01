<?php

require_once('controllers/abstract-controller.class.php');

class Route
{
	private $path = "";
	private $controller = null;
	private $parameters = array();

	/**
	 * @param string $path
	 * @param \AbstractController $controller
	 * @var \Route $new_route
	 */
	public function __construct($path, $controller)
	{
		$this->path = $path;
		$this->controller = $controller;
	
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

	public function __get($name)
	{
		if (property_exists($this, $name))
			return $this->$name;
		else
			throw new Exception("Error: property " . $name . " invalid !");
	}

	/**
	 * @param string $name
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	public function render()
	{
		$this->controller->render($this->parameters);
	}
}

?>