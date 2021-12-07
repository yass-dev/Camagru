<?php

class PublicationController
{
	public static function like($parameters = [])
	{
		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(LikedPublication::class);
		$items = $repo->find(['user_id', 'publication_id']);
		var_dump($items);
	}

	public static function addLike($parameters = [])
	{
		if (Session::get('user_id') == NULL)
			throw new ForbiddenException();

		$publication_id = $parameters['id'];
		
		$orm = $GLOBALS['orm'];
		$publication_repo = $orm->getRepository(Publication::class);

		if ($publication_repo->find([], ['id' => $publication_id]) == NULL)
			throw new NotFoundException("Publication not found");

		$liked_repo = $orm->getRepository(LikedPublication::class);
		$new = new LikedPublication();
		$new->user_id = Session::get('user_id');
		$new->publication_id = $publication_id;
		$liked_repo->insert($new);
	}
}

?>