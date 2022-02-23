<?php

class ORMConstraint
{
	public $name;
	public $key_name;
	public $entity_name;
	public $entity_var;

	public function __construct($name, $key, $entity, $var)
	{
		$this->name = $name;
		$this->key_name = $key;
		$this->entity_name = $entity;
		$this->entity_var = $var;
	}
}

?>