<?php

class PublicationComment extends Entity
{
	public $id;
	public $user_id;
	public $publication_id;
	public $comment;
	public $date;
	public $user;

	public function __construct()
	{
		parent::__construct('publication_comment');
		$this->addColumn('user_id', Column::NUMBER_TYPE);
		$this->addColumn('publication_id', Column::NUMBER_TYPE);
		$this->addColumn('comment', Column::TEXT_TYPE);
		$this->addColumn('date', Column::DATE_TYPE);
		$this->addConstraint('user', 'user_id', 'user', 'id');
		$this->addConstraint('publication', 'publication_id', 'publication', 'id');
	}
}

?>