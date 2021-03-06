<?php

class Publication extends Entity
{
	public $id;
	public $user_id;
	public $path;
	public $date;
	public $user;
	public $likes;
	public $comments;

	public function __construct()
	{
		parent::__construct('publication');
		$this->addColumn('path', Column::TEXT_TYPE, false);
		$this->addColumn('user_id', Column::NUMBER_TYPE, false);
		$this->addColumn('date', Column::DATETIME_TYPE, false);
		$this->addConstraint('user', 'user_id', 'user', 'id');
	}

	public function isLikedByUser()
	{
		foreach ($this->likes as $like)
			if ($like->user_id == Session::get('user_id'))
				return true;
		return false;
	}
}

?>