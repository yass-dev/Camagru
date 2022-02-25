<?php

class Mailer
{
	const MAILER_FROM_GLOBAL_NAME = 'Yass/Mailer/From';

	public static function setFrom($from)
	{
		$GLOBALS[Mailer::MAILER_FROM_GLOBAL_NAME] = $from;
	}

	public static function sendRaw($to, $subject, $message)
	{
		$headers = [
			"Content-type:text/html;charset=UTF-8",
			"MIME-Version: 1.0",
			"From: <" . $GLOBALS[Mailer::MAILER_FROM_GLOBAL_NAME] . ">"
		];
		$headers = implode("\r\n", $headers);
		return mail($to, $subject, $message, $headers);
	}

	public static function sendHtml($to, $subject, $message)
	{
		$html = "
		<html>
			<head>
				<title>$subject</title>
			</head>
			<body>
				$message
			</body>
		</html>
		";

		$headers = [
			"Content-type: text/html;charset=UTF-8",
			"MIME-Version: 1.0",
			"From: <" . $GLOBALS[Mailer::MAILER_FROM_GLOBAL_NAME] . ">"
		];
		$headers = implode("\r\n", $headers);

		return mail($to, $subject, $html, $headers);
	}
}

?>