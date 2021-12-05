<?php

require_once('orm/column.php');

abstract class Entity
{
	private $name;
	private $columns;

	public function __construct(string $name = null)
	{
		$this->name = ($name == null ? get_class($this) : $name);
		$this->columns = array();
	}

	public function generateQuery()
	{
		$query = "CREATE TABLE $this->name (";
		$query .= "id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,";
		for ($i = 0; $i < count($this->columns); $i++)
		{
			$column = $this->columns[$i];
			$query .= $column->generateCreationLine();
			// If this is not the last column
			if ($i != count($this->columns) - 1)
				$query .= ', ';
		}
		$query .= ")";
		return $query;
	}

	protected function addColumn(string $name, string $type, bool $nullable = false)
	{
		$column = new Column($name, $type, $nullable);
		array_push($this->columns, $column);
	}

	public function getName()
	{
		return $this->name;
	}
}

?>