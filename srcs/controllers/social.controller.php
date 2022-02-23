<?php

class SocialController
{
	public static function shareTwitter($parameters = [])
	{
		if (!$parameters['id'])
			throw new BadRequestException();

		$id = $parameters['id'];
		
		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publication = $repo->findOne(['path'], ['id' => $id]);
		if (!$publication)
			throw new NotFoundException("Publication not found");

		TemplateEngine::render(['views/social/twitter.view.php']);
	}
}

?>