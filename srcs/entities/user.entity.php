<?php

require_once('YassFramework/orm/entity.php');

class User extends Entity
{
	public $id;
	public $username;
	public $email;
	public $password;
	public $activated;
	public $publications;

	public function __construct()
	{
		parent::__construct('user');
		$this->addColumn('username', Column::TEXT_TYPE, false);
		$this->addColumn('email', Column::TEXT_TYPE, false);
		$this->addColumn('password', Column::VARCHAR_TYPE, false);
		$this->addColumn('activated', Column::BOOL_TYPE, false, 'FALSE');
		
		$this->addRelation(Relation::ONE_TO_MANY, 'user', 'publication', 'publications', 'user_id');
	}
}

?>