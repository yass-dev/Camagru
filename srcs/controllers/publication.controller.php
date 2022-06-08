<?php

require_once('utils/Image.php');

class PublicationController
{
	public static function gallerieView($parameters = [])
	{
		TemplateEngine::render();
	}

	public static function getAllPublications($parameters = [])
	{
		$validator = new DataValidator();
		$validator->setArray($_GET);
		$validator->addConstraint('page', [Constraint::class, "IS_INT"]);
		$validator->addConstraint('page', [Constraint::class, "IS_STRICTLY_POSITIVE"]);
		if (!$validator->validate())
			throw new BadRequestException($validator->error_message);

		$limit = 5;
		$offset = (intval($_GET['page']) - 1) * $limit;
		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publications = $repo->findAllWithRelations($limit, $offset);

		echo json_encode($publications);
	}

	public static function createNew($parameters = [])
	{
		// CHECK STICKJER SIZE POST !!!!!!
		if (count($_FILES) != 1 || !isset($_FILES['webcam_image']) || !isset($_POST['stickers']))
			throw new BadRequestException("Bad parameters" . json_encode($_POST));

		$stickers = json_decode($_POST['stickers']);

		if ($_FILES['webcam_image']['error'])
			throw new BadRequestException("Bad base image." . $_FILES['webcam_image']['error']);

		$tmp_webcam = $_FILES['webcam_image']['tmp_name'];
		$webcam_img = imagecreatefromfile($tmp_webcam);
		if ($webcam_img == null)
			throw new BadRequestException("Base image is not an image.");
		imagealphablending($webcam_img, true);
		imagesavealpha($webcam_img, true);

		foreach ($stickers as $sticker_data)
		{
			$tmp_sticker = '.' . $sticker_data->img;

			$sticker_img = imagecreatefromfile($tmp_sticker);
			if ($sticker_img == null)
				throw new BadRequestException("Bad sticker");

			$sticker_x = $sticker_data->x;
			$sticker_y = $sticker_data->y;

			if (!file_exists($tmp_sticker))
				throw new NotFoundException("Sticker not found : " . $tmp_sticker);

			// Get sticker info
			$sticker_info = getimagesize($tmp_sticker);	
			$sticker_width = $sticker_info[0];
			$sticker_height = $sticker_info[1];
			$sticker_type = $sticker_info['mime'];

			if ($sticker_type == 'image/png')
			{
				imagealphablending($sticker_img, true);
				imagesavealpha($sticker_img, true);
			}

			imagecopy($webcam_img, $sticker_img, $sticker_x, $sticker_y, 0, 0, $sticker_width, $sticker_height);
			imagedestroy($sticker_img);
		}

		$new_filename = uniqid('p_' . Session::get('user_id'));
		$new_filename = 'static/publications/' . $new_filename;

		$new_filename .= '.png';
		imagepng($webcam_img, $new_filename);

		$publication = new Publication();
		$publication->user_id = Session::get('user_id');
		$publication->path = '/' . $new_filename;
		$publication->date = date('Y-m-d H:i:s');
		
		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publication = $repo->insert($publication);

		imagedestroy($webcam_img);

		echo json_encode($publication);
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
		
		$user_id = Session::get('user_id');

		$liked_repo = $orm->getRepository(LikedPublication::class);

		if ($liked_repo->findOne(['id'], ['user_id' => $user_id, 'publication_id' => $publication_id]) != NULL)
			throw new ForbiddenException("You have already liked this publication");

		$new = new LikedPublication();
		$new->user_id = $user_id;
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

		$user = $orm->getRepository(User::class)->findOne(['email', 'mail_enabled'], ['id' => $publication->user_id]);
		$comment_author = $orm->getRepository(User::class)->findOne(['username'], ['id' => Session::get('user_id')]);
		if (boolval($user->mail_enabled))
			Mailer::sendRaw($user->email, "New comment", "$comment_author->username has commented your publication with '$new->comment'");

		echo json_encode($publication);
	}

	public static function removePublication($parameters)
	{
		if (!isset($parameters['id']))
			throw new BadRequestException();
		$publication_id = $parameters['id'];
		
		if (Session::get('user_id') == NULL)
			throw new ForbiddenException("You must be logged in to remove a publication.");

		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publication = $repo->findOne([], ['id' => $publication_id]);
		if ($publication == NULL)
			throw new NotFoundException("Publication $publication_id not found.");

		if ($publication->user_id != Session::get('user_id'))
			throw new ForbiddenException("You cannot remove this publication.");
		
		$repo->removePublication($publication);
		echo "Publication deleted sucessfully.";
	}

	public static function getPublication($parameters)
	{
		if (!isset($parameters['id']))
			throw new BadRequestException();
		$publication_id = $parameters['id'];

		$orm = $GLOBALS['orm'];
		$repo = $orm->getRepository(Publication::class);
		$publication = $repo->findOneWithRelations($publication_id);
		if ($publication == NULL)
			throw new NotFoundException("Publication not found.");
		
		echo json_encode($publication);
	}
}

?>