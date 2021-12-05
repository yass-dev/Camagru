class Ajax
{
	get(url, callback)
	{
		const xmlhttp = new XMLHttpRequest();
		xmlhttp.onload = callback;
		xmlhttp.open("GET", url);
		xmlhttp.send();
	}

	post(url, data, callback)
	{
		const xmlhttp = new XMLHttpRequest();
		xmlhttp.onload = callback;
		xmlhttp.open("POST", url);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		let params = "";
		for (let i = 0; i < Object.keys(data).length; i++)
		{
			let key = Object.keys(data)[i];
			params += `${key}=${data[key]}`;
			if (i < Object.keys(data).length - 1)
				params += '&';
		}
		xmlhttp.send(params);
	}
}