<?php

class Column
{
	public const TEXT_TYPE = "text";
	public const VARCHAR_TYPE = "varchar(255)";
	public const NUMBER_TYPE = "int";
	public const DATE_TYPE = "date";
	public const DATETIME_TYPE = "DATETIME";
	public const BOOL_TYPE = "tinyint(1)";

	private $name;
	private $type;
	private $nullable;
	private $default;

	public function __construct($name, $type, $nullable, $default)
	{
		$this->name = $name;
		$this->type = $type;
		$this->nullable = $nullable;
		$this->default = $default;
	}

	public function generateCreationLine()
	{
		$null_str = ($this->nullable ? "" : "NOT NULL");

		$default_str = "";
		if ($this->default !== NULL && $this->type == Column::TEXT_TYPE || $this->type == Column::VARCHAR_TYPE)
			$default_str = "DEFAULT '$this->default'";
		else if ($this->default !== NULL)
			$default_str = "DEFAULT $this->default";
		
		return "$this->name $this->type $null_str $default_str";
	}

	public function getName()
	{
		return $this->name;
	}
}

?>