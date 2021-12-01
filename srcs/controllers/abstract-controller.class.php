<?php

abstract class AbstractController
{
	private $view;
	private $data;
	protected $page_name = "salut";
	protected $parameters;

	/**
	 * @param string $view
	 */
	public function __construct($view = null)
	{
		$this->view = $view;
		$this->data = array();
	}

	/**
	 * Render a view if view is not null
	 * Else return json
	 */
	public function render($parameters)
	{
		$this->parameters = $parameters;
		$this->execute();
		if ($this->view != null)
		{
			ob_start();
			include($this->view);
			$content = ob_get_clean();
			include('views/base/base.view.php');
		}
		else
			echo json_encode($this->data);
	}

	abstract protected function execute();
}

?>