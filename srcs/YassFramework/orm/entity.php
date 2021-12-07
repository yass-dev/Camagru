<?php

require_once('YassFramework/orm/column.php');
require_once('YassFramework/orm/constraint.php');

abstract class Entity
{
	private $name;
	private $columns;
	private $constraints;
	private $relations;

	private $__friends = array('Repository', 'Entity');

	public function __construct(string $name = null)
	{
		$this->name = ($name == null ? get_class($this) : $name);
		$this->columns = array();
		$this->constraints = array();
		$this->relations = array();
	}

    public function __get($key)
    {
        $trace = debug_backtrace();
        if(isset($trace[1]['class']) && in_array($trace[1]['class'], $this->__friends)) {
            return $this->$key;
        }
        trigger_error('Cannot access private property ' . __CLASS__ . '::$' . $key, E_USER_ERROR);
    }

    public function __set($key, $value)
    {
        $trace = debug_backtrace();
        if(isset($trace[1]['class']) && in_array($trace[1]['class'], $this->__friends)) {
            return $this->$key = $value;
        }
        trigger_error('Cannot access private property ' . __CLASS__ . '::$' . $key, E_USER_ERROR);
    }

	public function generateQuery()
	{
		$query = "CREATE TABLE $this->name (";
		$query .= "id int AUTO_INCREMENT PRIMARY KEY,";
		
		// Set columns
		for ($i = 0; $i < count($this->columns); $i++)
		{
			$column = $this->columns[$i];
			$query .= $column->generateCreationLine();

			if ($i != count($this->columns) - 1)
				$query .= ', ';
		}
		
		// Set FOREIGN KEY
		foreach ($this->constraints as $constraint)
			$query .= ", CONSTRAINT $constraint->name FOREIGN KEY ($constraint->key_name) REFERENCES $constraint->entity_name ($constraint->entity_var)";

		$query .= ")";
		return $query;
	}

	protected function addColumn(string $name, string $type, bool $nullable = false, $default = NULL)
	{
		$column = new Column($name, $type, $nullable, $default);
		array_push($this->columns, $column);
	}

	protected function addConstraint($name, $key_name, $entity, $entity_var)
	{
		$constraint = new ORMConstraint($name,$key_name, $entity, $entity_var);
		array_push($this->constraints, $constraint);
	}

	protected function addRelation($type, $a, $b, $a_property, $b_property)
	{
		$relation = new Relation($type, $a, $b, $a_property, $b_property);
		array_push($this->relations, $relation);
	}

	public function getConstraints()
	{
		return $this->constraints;
	}

	public function getRelations()
	{
		return $this->relations;
	}

	public function getTableName()
	{
		return $this->name;
	}

	public function getColumnNames()
	{
		$ret = array();
		foreach ($this->columns as $column)
			array_push($ret, $column->getName());
		return $ret;
	}
}

?>