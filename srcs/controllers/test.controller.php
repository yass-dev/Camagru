<?php

require_once('YassFramework/template-engine/template-engine.php');

class TestController
{
	public static function testIndex($parameters = [])
	{	
		TemplateEngine::render(['views/test.view.php'], $parameters);
	}
}

?>