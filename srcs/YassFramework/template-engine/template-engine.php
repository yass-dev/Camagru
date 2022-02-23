<?php

class TemplateEngine
{
	const BASE_TEMPLATE_VAR_NAME = 'template-engine/base_file';

	public static function setBaseTemplate($base)
	{
		$GLOBALS[TemplateEngine::BASE_TEMPLATE_VAR_NAME] = $base;
	}

	public static function render($subviews = [], $parameters = [])
	{
		ob_start();
		foreach ($subviews as $subview)
			include($subview);
		$content = ob_get_clean();

		include($GLOBALS[TemplateEngine::BASE_TEMPLATE_VAR_NAME]);
	}
}

?>