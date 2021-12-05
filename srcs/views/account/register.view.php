<div class="register">
	<h1>Register</h1>
	<form class="register_form">
		<div class="field">
			<input type="text" id="username" autocomplete/>
			<label for="username">Username</label>
		</div>
		<div class="field">
			<input type="mail" id="email" autocomplete/>
			<label for="email">Email</label>
		</div>
		<div class="field">
			<input type="password" id="password" autocomplete/>
			<label for="password">Password</label>
		</div>
		<div class="button" id="register_button">Register</div>
	</form>
</div>

<script>

let inputs = document.getElementsByTagName('input');
for (let element of inputs)
{
	element.addEventListener('input', function(e)
	{
		if (element.value != "")
			element.classList.add('active')
		else
			element.classList.remove('active');
	})
}


let register_button = document.getElementById('register_button');
register_button.addEventListener('click', function()
{
	let username = document.getElementById('username').value;
	let email = document.getElementById('email').value;
	let password = document.getElementById('password').value;

	let ajax = new Ajax();
	ajax.post('/api/account/register', {username: username, email: email, password: password}, function(data)
	{

	})
})

</script>

<style>

.register
{
	margin: 0 auto;
	width: 15rem;
	max-width: 100%;
}

h1
{
	font-weight: normal;
	width: 100%;
	text-align: center;
}

.field
{
	position: relative;
	margin: 1rem 0;
	padding: 0.5rem 0;
	width: 100%;
}

input
{
	padding: 0.25rem 0.5rem;
	font-size: 1rem;
	outline: none;
	border: none;
	border-bottom: solid 1px #00000033;
	transition: all 0.25s;
	width: 100%;
}

input:focus
{
	border-bottom: solid 1px ;
}

label
{
	position: absolute;
	top: 0.75rem;
	left: 0.5rem;
	font-size: 1rem;
	color: #454545;
	cursor: text;
	width: 100%;
	transition: all 0.25s;
}

input:focus ~ label,
input.active ~ label
{
	top: -0.5rem;
}

#register_button
{
	padding: 0.5rem 0.5rem;
    width: 10rem;
    margin: 0.5rem auto;
    text-align: center;
    cursor: pointer;
    border: solid 1px black;
    transition: all 0.25s;
}

</style>