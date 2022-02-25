<?php

class LikedPublication extends Entity
{
	public $id;
	public $user_id;
	public $publication_id;

	public function __construct()
	{
		parent::__construct('liked_publication');
		$this->addColumn('user_id', Column::NUMBER_TYPE);
		$this->addColumn('publication_id', Column::NUMBER_TYPE);
		$this->addConstraint('user_fk', 'user_id', 'user', 'id');
		$this->addConstraint('publication_fk', 'publication_id', 'publication', 'id');
	}
}

?>