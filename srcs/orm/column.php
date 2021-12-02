<?php

class Column
{
	public const TEXT_TYPE = "text";
	public const VARCHAR_TYPE = "varchar(255)";
	public const NUMBER_TYPE = "int";
	public const DATE_TYPE = "date";
	public const BOOL_TYPE = "tinyint(1)";

	private $name;
	private $type;
	private $nullable;

	public function __construct($name, $type, $nullable)
	{
		$this->name = $name;
		$this->type = $type;
		$this->nullable = $nullable;
	}

	public function generateCreationLine()
	{
		$null_str = ($this->nullable ? "" : "NOT NULL");
		return "$this->name $this->type $null_str";
	}
}

?>