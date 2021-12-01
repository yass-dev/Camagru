<?php

require_once('controllers/abstract-controller.class.php');

class TestController extends AbstractController
{
	public function __construct()
	{
		parent::__construct('views/test.view.php');
	}

	protected function execute()
	{
		$this->page_name = "test page name";
	}
}

?>