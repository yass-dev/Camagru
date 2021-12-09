class Ajax
{
	get(url)
	{
		return fetch(url);
	}

	post(url, body)
	{
		let data = new FormData();
		for (let i = 0; i < Object.keys(body).length; i++)
		{
			let key = Object.keys(body)[i];
			data.append(key, body[key])
		}

		return fetch(url, {
			method: 'POST',
			body: data
		});
	}

	delete(url)
	{
		return fetch(url,
		{
			method: 'DELETE'
		});
	}
}