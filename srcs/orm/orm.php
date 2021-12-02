<?php

require_once('orm/entity.php');

class ORM
{
	private $db;
	private $entites;
	private $db_exists;

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
		// Init tables
		if ($this->db_exists == false)
		{
			foreach ($this->entites as $entity)
			{
				$query = $entity->generateQuery();
				$this->db->exec($query);
			}
		}
	}

	public function connect($host, $dbname, $username, $password)
	{
		try
		{
			$this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", "$username", "$password");
			$query = $this->db->query("SHOW DATABASES LIKE '$dbname';");
			$this->db_exists = $query->fetch() != false;
			$query->closeCursor();
		}
		catch (Exception $e)
		{
			die($e->getMessage());
		}
	}
}

?>