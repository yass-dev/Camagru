class Ajax
{
	query(url, method, body = null, headers = {})
	{
		return new Promise((resolve, reject) =>
		{
			let payload = {
				method: method,
				headers: headers
			}

			if (body)
				payload['body'] = body;

			fetch(url, payload)
			.then(async res =>
			{
				let text = await res.text();
				
				try
				{
					text = JSON.parse(text);
				}
				catch (e)
				{
					text = text;
				}

				let ret = {
					status: res.status,
					data: text
				}
				if (res.ok)	resolve(ret);
				else		reject(ret);
			})
		});
	}

	get(url)
	{
		return this.query(url, "GET");
	}

	post(url, body, headers = {})
	{
		let data = new FormData();
		for (let key in body)
			data.append(key, body[key]);
		return this.query(url, "POST", data, headers);
	}

	put(url, body)
	{
		let data = JSON.stringify(body);
		return this.query(url, "PUT", data);
	}

	delete(url)
	{
		return this.query(url, "DELETE");
	}
}