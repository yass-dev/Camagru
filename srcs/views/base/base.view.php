<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<link rel="stylesheet" href="/static/css/base.css"/>
		<script src="/static/js/ajax.js"></script>
		<script src="/static/js/yass.js"></script>
		<script src="/static/js/yass-router.js"></script>
		<script src="/static/js/yass-store.js"></script>
		<title>Camagru | <?= isset($page_name) ? $page_name : "" ?></title>
	</head>
	<body>
		<noscript>
			<strong>We're sorry but our app doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
		</noscript>
		<div id="app">
			<App></App>
		</div>
		<YassComponent link="/static/components/App.yass"></YassComponent>
		<YassComponent link="/static/components/Header.yass"></YassComponent>
		<YassComponent link="/static/components/Gallerie.yass"></YassComponent>
		<YassComponent link="/static/components/Publication.yass"></YassComponent>
		<YassComponent link="/static/components/SocialModal.yass"></YassComponent>
		<YassComponent link="/static/components/PublicationView.yass"></YassComponent>
		<YassComponent link="/static/components/UserView.yass"></YassComponent>
		<YassComponent link="/static/components/Login.yass"></YassComponent>
		<YassComponent link="/static/components/Register.yass"></YassComponent>
		<YassComponent link="/static/components/ForgottenPassword.yass"></YassComponent>
		<YassComponent link="/static/components/RestorePassword.yass"></YassComponent>
		<YassComponent link="/static/components/SettingsPopup.yass"></YassComponent>
		<YassComponent link="/static/components/Builder.yass"></YassComponent>
		<YassComponent link="/static/components/Footer.yass"></YassComponent>
		<script>
			const yass = new Yass('app', [YassRouter, YassStore]);

			yass.plugins.router.addRoute('index', '/', "Gallerie");
			yass.plugins.router.addRoute('builder', '/builder', "Builder", () =>
			{
				if (!yass.plugins.store.user.is_logged_in)
				{
					alert("You must be logged in to access to this page");
					yass.plugins.router.push("/login");
					return false;
				}
				return true;
			});
			yass.plugins.router.addRoute('user_view', '/users/:username', 'UserView');
			yass.plugins.router.addRoute('login', '/login', 'Login');
			yass.plugins.router.addRoute('register', '/register', 'Register');
			yass.plugins.router.addRoute('forgotten_password', '/forgotten_password', 'ForgottenPassword');
			yass.plugins.router.addRoute('restore_password', '/restore_password/:restore_id', 'RestorePassword');
			yass.plugins.router.addRoute('publication_view', '/publications/:id', 'PublicationView');

			yass.plugins.store.addStore('user', {
				is_logged_in: false,
				id: -1,
				username: '',
				email: ''
			})
		</script>
	</body>
</html>
