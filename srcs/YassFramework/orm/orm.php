<?php

require_once('YassFramework/orm/entity.php');
require_once('YassFramework/orm/repository.php');
require_once('YassFramework/orm/relation.php');

class ORM
{
	private $db;
	private $entites;
	private $repositories;

	public function __construct()
	{
		$this->entites = array();
		$this->repositories = array();
	}

	public function registerEntity(Entity $entity)
	{
		array_push($this->entites, $entity);
	}

	private function tableExists($table)
	{
		try
		{
			$result = $this->db->query("SELECT 1 FROM {$table} LIMIT 1");
		}
		catch (Exception $e)
		{
			return FALSE;
		}
		return $result !== FALSE;
	}

	public function init()
	{
		foreach ($this->entites as $entity)
		{
			if (!$this->tableExists($entity->getTableName()))
			{
				$query = $entity->generateQuery();
				try
				{
					$this->db->exec($query);
				}
				catch (Exception $e)
				{
					echo $query . '<br/>';
					throw $e;
				}
			}
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

	public function registerRepository($entity_class, $repository_class)
	{
		$this->repositories[$entity_class] = $repository_class;
	}

	/**
	 * @param string $name
	 * @return Repository
	 */
	public function getRepository(string $name)
	{
		if (array_key_exists($name, $this->repositories))
			return new $this->repositories[$name]($this->db);
		return new Repository($this->db, $name);
	}

	public function execQuery($query)
	{
		$query = $this->db->prepare($query);
		$params = func_get_args();
		array_splice($params, 0, 1);
		return $query->execute($params)->fetchAll();
	}
}

?>