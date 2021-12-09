class Component
{
	name = ""
	data = {};
	methods = {};
	template = "";
	mounted = () => {};
	updated = () => {};

	constructor({name, data, methods, template, mounted, updated})
	{
		this.name = name;
		this.data = data;
		this.methods = methods ? methods : {};
		this.template = template ? template : document.querySelector(`template#${name}`).innerHTML;
		this.mounted = mounted ? mounted : () => {};
		this.updated = updated ? updated : () => {};
	}

	escape(text)
	{
		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}
}

class YassNode
{
	node = null;
	parent_node = null;
	instance = null;
	children = []
	attached_proxy_item = [];
	require_update = true;
	html = '';
	is_removed = false;

	constructor(node, parent_node, instance)
	{
		// console.log(`Construct ${node.nodeName}`)
		this.node = node;
		this.parent_node = parent_node;
		this.children = [];
		this.html = node.innerHTML;
		this.instance = instance;
		if (parent_node)
			this.parent_node.children.push(this);
	}

	handleAttributes(attr_name, attr_value)
	{
		let elem = this.node;
		let instructions = attr_name.substr(5).split(':');	// delete 'yass-'
		if (instructions[0] == 'on' && this.instance.render_id == 0)
		{
			elem.addEventListener(instructions[1], e =>
			{
				this.instance.proxy.$event = e;
				return evalInContext(attr_value, this.instance.proxy);
			})
		}
		else if (instructions[0] == 'if')
		{
			if (evalInContext(attr_value, this.instance.proxy) == false)
			{
				elem.remove();
				this.is_removed = true;
			}
			else if (this.is_removed == true)
			{
				console.log("Evalutate v-if on", elem, evalInContext(attr_value, this.instance.proxy))
				let new_node = document.createElement('div');
				new_node.innerHTML = this.html.trim();
				new_node = new_node.firstChild;
				let parent = this.parent_node.node;
				parent.insertBefore(new_node, this.node);
				this.node.remove();
				this.node = new_node;
			}
		}
		else if (instructions[0] == 'show')
		{
			if (evalInContext(attr_value, this.instance.proxy) == false)
				elem.style.display = 'none';
		}
		else if (instructions[0] == "ref")
			this.instance.proxy.refs[attr_value] = elem;
	}

	handleDynamicParams(attr_name, attr_value)
	{
		let elem = this.node;
		if (attr_name == 'class')
		{
			let class_list = evalInContext(`(() => {return ${attr_value}})()`, this.instance.proxy);
			for (let class_name of Object.keys(class_list))
			{
				if (!class_list[class_name] && elem.classList.contains(class_name))
					elem.classList.remove(class_name);
				else if (class_list[class_name] && !elem.classList.contains(class_name))
					elem.classList.add(class_name)
				elem.removeAttribute(`:${attr_name}`);
			}
		}
		else if (!isComponent(elem))
		{
			elem.setAttribute(attr_name, evalInContext(attr_value, this.instance.proxy));
			elem.removeAttribute(`:${attr_name}`);
		}
		else if (isComponent(elem))
			elem.setAttribute(`:${attr_name}`, JSON.stringify(evalInContext(`(() => {return ${attr_value}})()`, this.instance.proxy)));
	}

	handleDynamicText()
	{
		let elem = this.node;
		elem.textContent = elem.textContent.replaceAll(/{{([^{][\s\S]+?[^}])}}/g, (match) => evalInContext(match, this.instance.proxy));
	}

	handleDirectives()
	{
		console.log(`Handle directives of`, this.node);
		let elem = this.node;
		if (elem.nodeName.toLowerCase() == "#text")
			this.handleDynamicText();
		else if (elem.nodeName == "YASS-SLOT")
			elem.outerHTML = this.slot;
		if (elem.attributes)
		{
			for (let attr of elem.attributes)
			{
				if (attr.name.startsWith('@'))
					this.handleAttributes('yass-on:' + attr.name.substr(1), attr.value);
				else if (attr.name.startsWith('yass-'))
					this.handleAttributes(attr.name, attr.value);
				else if (attr.name[0] == ':')
					this.handleDynamicParams(attr.name.substr(1), attr.value);			// To delete the ':' (:class, :href...)
			}
		}
	}

	attachProxyItem(name)
	{
		this.attached_proxy_item.push(name);
	}

	render()
	{
		this.handleDirectives();
		this.instance.checkChildren(this.node);
		for (let child of this.children)
			child.render();
	}
}

class ComponentInstance
{
	node = null
	nodes = [];
	parent = null;
	component = null;
	proxy = null;
	render_id = 0;
	slot = "";
	props = {};
	refs = {};

	constructor(node, component, parent = null, is_root = false)
	{
		this.slot = node.innerHTML;
		this.node = node;
		this.component = component;
		this.parent = parent;
		this.extractProps();
		this.createProxy(component);
		if (!is_root)
			this.replaceNode();
		this.createYassNodes(this.node, null);
		// this.checkChildren(this.node)
	}

	extractProps()
	{
		let attributes = this.node.attributes;
		for (let attribute of attributes)
		{
			if (attribute.name[0] == ':')
			{
				console.log(attribute.name, evalInContext(`(() => {return ${attribute.value}})()`, this.proxy))
				this.props[attribute.name.substr(1)] =  evalInContext(`(() => {return ${attribute.value}})()`, this.proxy);//JSON.parse(attribute.value);
			}
			else
				this.props[attribute.name] = attribute.value;
		}
	}

	createProxy(component)
	{
		this.proxy = new Proxy(
		{
			name: component.name,
			props: this.props,
			refs: this.refs,
			...component.data(),
			...component.methods,
			mounted: component.mounted,
			updated: component.updated
		},
		{
			get: function(target, property)
			{
				return target[property];
			},
			set: (target, property, value) =>
			{
				target[property] = value;
				this.render();
				return true
			}
		});
	}
	
	replaceNode()
	{
		let new_node = document.createElement('div');
		new_node.innerHTML = this.component.template.trim();
		new_node = new_node.firstChild;
		let parent = this.node.parentNode;
		parent.insertBefore(new_node, this.node);
		this.node.remove();
		this.node = new_node;
	}

	checkChildren(elem)
	{
		for (let child of elem.childNodes)
		{
			let component = findComponent(child.nodeName);
			if (component)
			{
				let instance = new ComponentInstance(child, component, parent);
				console.log("new instance on", child)
				instance.render(child);
			}
			this.checkChildren(child);
		}
	}

	createYassNodes(elem, parent)
	{
		let yass_node = new YassNode(elem, parent, this);
		this.nodes.push(yass_node)
		for (let child of elem.childNodes)
			this.createYassNodes(child, yass_node);
	}

	renderYassNodes()
	{
		for (let node of this.nodes)
			node.render();
	}

	callHook(name)
	{
		if (this.proxy[name])
			this.proxy[name]();
	}

	render()
	{
		// console.log(this.proxy);
		if (this.render_id == 0)	this.callHook('mounted');
		else						this.callHook('updated')
		this.renderYassNodes();
		this.render_id++;
	}
}

let components = new Array();

function evalInContext(str, context)
{
	return function()
	{
		return eval(str);
	}.call(context);
}

function isComponent(elem)
{
	return findComponent(elem.nodeName) != null;
}

function registerComponent(component)
{
	if (components.find(c => c.name == component.name) === undefined)
	{
		component = new Component(component);
		components.push(component);
	}
	else
		console.log("Component already registered");
}

function findComponent(name)
{
	for (let component of components)
	{
		if (component.name.toLowerCase() == name.toLowerCase())
			return component;
	}
	return null;
}

function createRootComponent(id)
{
	let root = document.getElementById(id);
	let root_instance = new ComponentInstance(root, {name: "Root", template: root.innerHTML, data: ()=>{}, mounted: () => {}}, null, true);
	root_instance.render();
}

function mount(id)
{
	createRootComponent(id);
}

async function loadComponents()
{
	return new Promise(async (resolve, reject) =>
	{
		let yass_components = document.getElementsByTagName('YassComponent');
		for (let component of yass_components)
		{
			await fetch(component.getAttribute('link'))
			.then(async res =>
			{
				text = await res.text();

				let container = document.createElement('div');
				container.innerHTML = text;

				let template = container.getElementsByTagName('template')[0];
				if (!template)
					throw new Error("template tag missing in component");
				document.getElementsByTagName('body')[0].appendChild(template);

				let script = container.getElementsByTagName('script')[0];
				if (!script)
					throw new Error("script tag missing in component");
				eval(script.text);

				let style = container.getElementsByTagName('style')[0];
				if (!style)
					throw new Error("style tag missing in component");
				document.getElementsByTagName('body')[0].appendChild(style);
			});
		}
		resolve();
	})
}

document.addEventListener('yass_components_loaded', e =>
{
	mount('app');
});

window.addEventListener('load', async () =>
{
	await loadComponents();
	const event = new Event('yass_components_loaded');
	document.dispatchEvent(event);
})
