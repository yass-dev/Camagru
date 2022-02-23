<?php

require_once('YassFramework/orm/entity.php');

class Repository
{
	private $entity;
	private $db;

	public function __construct($db, string $entity_name)
	{
		$this->entity = new $entity_name();
		$this->db = $db;
	}

	public function find($select = [], $where = [], $relations = [], $order = [], $limit = NULL, $offset = 0)
	{
		$table_name = $this->entity->getTableName();
		$params = [];

		if (count($select) == 0)
			$select = $this->entity->getColumnNames();

		// Always select id
		array_push($select, 'id');
		$select = array_map(function ($s) use($table_name)
		{
			// If the table are already defined in the select component, no need to append the table_name
			if (str_contains($s, '.'))
				return "$s AS `$s`";
			else
				return "$table_name.$s AS `$table_name.$s`";
		}, $select);

		$select_list = ($select == '*' ? '*' : implode(',', $select));

		$query = " SELECT $select_list FROM $table_name ";

		foreach ($relations as $relation_name)
		{
			foreach ($this->entity->getRelations() as $relation)
			{
				if ($relation_name == $relation->a_property)
				{
					if ($relation->type == Relation::ONE_TO_MANY)
					{
						$query .= " INNER JOIN $relation->entity_b ON $table_name.id = $relation->alias_b.$relation->b_property ";
					}
				}
			}
		}

		$where_keys = array_keys($where);
		$where_values = array_values($where);
		if (count($where_keys) > 0)
		{
			$query .= " WHERE ";
			for ($i = 0; $i < count($where_keys); $i++)
			{
				$key = $where_keys[$i];
				$value = $where_values[$i];
				
				$query .= "$table_name.$key=:where_$key";
				$params['where_' . $key] = $value;

				if ($i < count($where_keys) - 1)
					$query .= " AND ";
			}
		}

		if (count($order) == 2)
		{
			$column = $order[0];
			$sort = $order[1];
			if (str_contains($column, '.'))
				$query .= " ORDER BY $column $sort";
			else
				$query .= " ORDER BY $table_name.$column $sort";
		}

		if ($limit != NULL)
			$query .= " LIMIT $limit OFFSET $offset";
		
		$req = $this->db->prepare($query);
		$req->execute($params);

		$result = $req->fetchAll();
		return $this->buildEntitiesFromQueryResult($result, $table_name);
	}

	private function buildEntitiesFromQueryResult($result, $table_name)
	{
		$entities = array();
		$classname = get_class($this->entity);
		$vars = array_keys(get_class_vars($classname));

		foreach ($result as $entry)
		{
			$entity = new $classname();
			foreach ($vars as $var_name)
			{
				if (isset($entry["$table_name.$var_name"]))
					$entity->$var_name = $entry["$table_name.$var_name"];
			}
			array_push($entities, $entity);
		}
		return $entities;
	}

	public function findOne($select = ['*'], $where = [], $relations = [])
	{
		$ret = $this->find($select, $where, $relations);
		return (count($ret) > 0 ? $ret[0] : NULL);
	}

	public function insert(Entity $entity)
	{
		$var_names = $entity->getColumnNames();
		$var_values = array();
		foreach ($var_names as $var)
			array_push($var_values, $entity->$var);

		$var_names_list = implode(',', $var_names);
		
		$prepared_var_values_list = implode(',', array_map(function($name)
		{
			return ':' . $name;
		}, $var_names));
		
		$table_name = $entity->getTableName();
		$query = "INSERT INTO $table_name ($var_names_list) VALUES($prepared_var_values_list)";
		$req = $this->db->prepare($query);
		for ($i = 0; $i < count($var_names); $i++)
			$req->bindParam($var_names[$i], $var_values[$i]);
		$req->execute();

		$id = $this->db->lastInsertId();
		$columns = array_map(function($c) use($table_name)
		{
			return "$table_name.$c";
		}, $this->entity->getColumnNames());
		
		return $this->findOne($columns, ['id' => $id]);
	}

	public function update(Entity $entity)
	{
		// UPDATE table SET a=:c, a=:v WHERE x=:y
		$var_names = $entity->getColumnNames();
		$var_values = array();
		foreach ($var_names as $var)
			array_push($var_values, $entity->$var);

		$edit_list = array_map(function($item)
		{
			return $item . ' = :set_' . $item;
		}, $var_names);
		
		$edit_list = implode(', ', $edit_list);

		$table_name = $entity->getTableName();

		$query = "UPDATE $table_name SET $edit_list WHERE id=:id";

		// $query = "INSERT INTO $table_name ($var_names_list) VALUES($prepared_var_values_list)";
		$req = $this->db->prepare($query);
		for ($i = 0; $i < count($var_names); $i++)
			$req->bindParam('set_'.$var_names[$i], $var_values[$i]);
		$req->bindParam('id', $entity->id);
		$req->execute();

		$id = $this->db->lastInsertId();
		
		return $this->findOne($entity->getColumnNames(), ['id' => $id]);
	}

	public function delete(Entity $entity)
	{
		$table_name = $entity->getTableName();
		$query = "DELETE FROM $table_name WHERE $table_name.id = :id";
		$query = $this->db->prepare($query);
		$query->execute(['id' => $entity->id]);
	}
}

?>