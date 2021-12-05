<?php

require_once('orm/entity.php');

class ORM
{
	private $db;
	private $entites;

	public function __construct()
	{
		$this->entites = array();
	}

	public function registerEntity(Entity $entity)
	{
		array_push($this->entites, $entity);
	}

	public function init()
	{
		foreach ($this->entites as $entity)
		{
			$query = $entity->generateQuery();
			$this->db->exec($query);
		}
	}

	public function connect($host, $dbname, $username, $password)
	{
		try
		{
			$this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", "$username", "$password");
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
}

?>