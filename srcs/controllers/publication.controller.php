<?php

class PublicationController
{
	public static function gallerieView($parameters = [])
	{
		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publications = $repo->findAllWithRelations();
		
		TemplateEngine::render(['views/gallerie/gallery_list.view.php'], ['publications' => $publications]);
	}

	public static function getAllPublications($parameters = [])
	{
		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publications = $repo->findAllWithRelations();
		echo json_encode($publications);
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
		$new = $liked_repo->insert($new);

		$publication = $publication_repo->findOneWithRelations($publication_id);
		echo json_encode($publication);
	}

	public static function unlike($parameters = [])
	{
		if (Session::get('user_id') == NULL)
			throw new ForbiddenException();

		$user_id = Session::get('user_id');
		$publication_id = $parameters['id'];
		
		$orm = $GLOBALS['orm'];
		$publication_repo = $orm->getRepository(Publication::class);

		if ($publication_repo->find([], ['id' => $publication_id]) == NULL)
			throw new NotFoundException("Publication not found");

		$liked_repo = $orm->getRepository(LikedPublication::class);
		$like = $liked_repo->findOne([], ['user_id' => $user_id]);
		if ($like == NULL)
			throw new ForbiddenException("You must like this publication to unlike it");
		$liked_repo->delete($like);
		$publication = $publication_repo->findOneWithRelations($publication_id);
		echo json_encode($publication);
	}

	public static function addComment($parameters = [])
	{
		if (Session::get('user_id') == NULL)
			throw new ForbiddenException();

		$publication_id = $parameters['id'];
		
		$orm = $GLOBALS['orm'];
		$publication_repo = $orm->getRepository(Publication::class);

		if ($publication_repo->find([], ['id' => $publication_id]) == NULL)
			throw new NotFoundException("Publication not found");

		$validator = new DataValidator();
		$validator->setArray($_POST);
		$validator->addConstraint('comment', [Constraint::class, "IS_STRING"]);
		$validator->addConstraint('comment', [Constraint::class, "IS_NOT_EMPTY"]);
		if (!$validator->validate())
			throw new BadRequestException();

		$comment_repo = $orm->getRepository(PublicationComment::class);
		$new = new PublicationComment();
		$new->user_id = Session::get('user_id');
		$new->publication_id = $publication_id;
		$new->comment = $_POST['comment'];
		$new->date = date("Y-m-d");
		$comment_repo->insert($new);
		$publication = $publication_repo->findOneWithRelations($publication_id);
		echo json_encode($publication);
	}
}

?>