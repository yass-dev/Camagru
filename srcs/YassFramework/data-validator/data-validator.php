<?php

require_once('YassFramework/data-validator/constraint.php');

class DataValidator
{
	private $array;
	private $constraints;
	public $error_message;

	public function setArray($array)
	{
		$this->array = $array;
		$this->constraints = array();
	}

	public function addConstraint($name, $constraint)
	{
		if (!isset($this->constraints[$name]))
			$this->constraints[$name] = array();
		array_push($this->constraints[$name], $constraint);
	}

	/**
	 * Check if all data are valid
	 * @return boolean
	 */
	public function validate()
	{
		$names = array_keys($this->constraints);

		foreach ($names as $name)
		{
			if (!isset($this->array[$name]))
				return false;
			$value = $this->array[$name];
			$constraints = $this->constraints[$name];
			foreach ($constraints as $constraint)
			{
				if (!$constraint($value))
				{
					$this->error_message = "Error on ${name} on constraint " . ((array)$constraint)[1];
					return false;
				}
			}
		}
		return true;
	}
}

?>