<?php

require_once('YassFramework/template-engine/template-engine.php');

class GallerieController
{
	/**
	 * @var Repository repository
	 */
	public static function gallerieView($parameters = [])
	{
		$orm = $GLOBALS['orm'];
		$repository = $orm->getRepository(Publication::class);

		$items = $repository->find(['id', 'path', 'user_id', 'date'], [], [], ['date', 'DESC']);

		TemplateEngine::render(['views/gallerie/gallery_list.view.php'], ['publications' => $items]);
	}

	
}

?>