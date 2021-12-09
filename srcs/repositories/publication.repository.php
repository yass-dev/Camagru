<?php

class PublicationRepository extends Repository
{
	public function __construct($db)
	{
		parent::__construct($db, Publication::class);
	}

	public function findAllWithRelations()
	{
		$orm = $GLOBALS['orm'];
		$user_repo = $orm->getRepository(User::class);
		$comment_repo = $orm->getRepository(PublicationComment::class);
		$like_repo = $orm->getRepository(LikedPublication::class);

		$publications = $this->find();
		foreach ($publications as $publication)
		{
			$publication->user = $user_repo->findOne(['username'], ['id' => $publication->user_id]);
			$publication->comments = $comment_repo->find(['comment', 'user_id'], ['publication_id' => $publication->id]);
			foreach ($publication->comments as $comment)
				$comment->user = $user_repo->findOne(['username'], ['id' => $comment->user_id]);
			$publication->likes = $like_repo->find(['user_id'], ['publication_id' => $publication->id]);
		}

		return $publications;
	}

	public function findOneWithRelations($id)
	{
		$orm = $GLOBALS['orm'];
		$user_repo = $orm->getRepository(User::class);
		$comment_repo = $orm->getRepository(PublicationComment::class);
		$like_repo = $orm->getRepository(LikedPublication::class);

		$publication = $this->findOne([], ['id' => $id]);
		if ($publication == NULL)
			return NULL;
		$publication->user = $user_repo->findOne(['username'], ['id' => $publication->user_id]);
		$publication->comments = $comment_repo->find(['comment', 'user_id'], ['publication_id' => $publication->id]);
		foreach ($publication->comments as $comment)
			$comment->user = $user_repo->findOne(['username'], ['id' => $comment->user_id]);
		$publication->likes = $like_repo->find(['user_id'], ['publication_id' => $publication->id]);

		return $publication;
	}
}

?>