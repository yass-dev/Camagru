<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<link rel="stylesheet" href="/static/css/base.css"/>
		<title><?= $this->page_name ?></title>
	</head>
	<body>
		<noscript>
			<strong>We're sorry but our app doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
		</noscript>
		<div id="app">
			<?php include('views/base/header.template.php') ?>
			<?= $content ?>
		</div>
	</body>
</html>
