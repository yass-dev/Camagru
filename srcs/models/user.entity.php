<?php

require_once('orm/entity.php');

class User extends Entity
{
	public function __construct()
	{
		parent::__construct("user");
		$this->addColumn('name', Column::TEXT_TYPE, false);
		$this->addColumn('email', Column::TEXT_TYPE, false);
		$this->addColumn('password', Column::VARCHAR_TYPE, false);
		$this->addColumn('age', Column::NUMBER_TYPE, false);
	}
}

?>