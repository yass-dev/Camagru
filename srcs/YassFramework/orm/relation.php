<?php

class Relation
{
	public $type;
	public $entity_a;
	public $entity_b;
	public $a_property;
	public $b_property;
	public $alias_a;
	public $alias_b;

	const ONE_TO_ONE = 'ONE_TO_ONE';
	const ONE_TO_MANY = 'ONE_TO_MANY';
	const MANY_TO_ONE = 'MANY_TO_ONE';
	const MANY_TO_MANY = 'MANY_TO_MANY';

	public function __construct($type, $entity_a, $entity_b, $a_property, $b_property)
	{
		$this->type = $type;
		$this->entity_a = $entity_a;
		$this->entity_b = $entity_b;
		$this->a_property = $a_property;
		$this->b_property = $b_property;
		$this->alias_a = "$entity_a";
		$this->alias_b = "$entity_b";
	}
}

?>