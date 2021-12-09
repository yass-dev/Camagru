<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<link rel="stylesheet" href="/static/css/base.css"/>
		<script src="/static/js/ajax.js"></script>
		<script src="/static/js/yass-framework-v2.js"></script>
		<title>Camagru | <?= isset($page_name) ? $page_name : "" ?></title>
	</head>
	<body>
		<noscript>
			<strong>We're sorry but our app doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
		</noscript>
		<div id="app">
			<Header></Header>
			<Gallerie></Gallerie>
		</div>
		<YassComponent link="/static/components/App.yass"></YassComponent>
		<YassComponent link="/static/components/Header.yass"></YassComponent>
		<YassComponent link="/static/components/Gallerie.yass"></YassComponent>
		<YassComponent link="/static/components/Publication.yass"></YassComponent>
		<script>
			const yass = new Yass('app');
		</script>
	</body>
</html>
