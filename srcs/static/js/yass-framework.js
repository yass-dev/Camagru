function handleAttributes(elem, attr_name, attr_value)
{
	let instructions = attr_name.substr(5).split(':');
	if (instructions[0] == 'on')
	{
		elem.addEventListener(instructions[1], e =>
		{
			eval(attr_value);
		})
	}
}

function searchYassElements(elements)
{
	for (let elem of elements)
	{
		if (elem.attributes)
		{
			for (let attr of elem.attributes)
			{
				if (attr.name.startsWith('@'))
					handleAttributes(elem, 'yass-on:' + attr.name.substr(1), attr.value);
				
				if (attr.name.startsWith('yass-'))
					handleAttributes(elem, attr.name, attr.value);
			}
		}

		searchYassElements(elem.childNodes);
	}
}

console.log("INIT");
let bodys = document.getElementsByTagName("body");
searchYassElements(bodys);