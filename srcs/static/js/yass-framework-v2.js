function evalInContext(str, context)
{
	return function()
	{
		return eval(str);
	}.call(context);
}

class Component
{
	name = "";
	props = {};
	data = () => ({});
	mehtods = {};
	mounted = () => {};
	updated = () => {};

	template = "";

	constructor({name, props, data, methods, mounted, updated})
	{
		this.name = name;
		this.props = props ? props : {},
		this.data = data ? data : () => ({});
		this.methods = methods ? methods : {};
		this.mounted = mounted ? mounted : () => {};
		this.updated = updated ? updated : () => {};
		this.template = document.querySelector(`template#${name}`).innerHTML;
	}
}

class ComponentInstance
{
	proxy = null;
	slot = "";
	data = {};
	props = {};
	refs = {};
	root_node = null;

	constructor(component)
	{
		this.component = component;
		this.createProxy(component)
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
				let excluded_property = ['$event'];
				target[property] = value;
				if (!excluded_property.includes(property))
					this.root_node.render();
				return true
			}
		});
	}

	callHook(name)
	{
		if (this.proxy[name])
			this.proxy[name]();
	}
}

class YassNode
{
	node = null
	children = [];
	must_render = true;
	render_id = 0;
	parent_instance = null;
	instance = null;
	children_instance = null;				// Component instance to pass to children
	initial_attributes = [];
	initial_text_content = "";

	is_component_root = false;				// If the node is a component
	initial_props_attributes = [];

	is_removed = false;
	removed_node = null;					// True

	constructor(elem, instance)
	{
		this.node = elem;
		this.parent_instance = instance;
		this.instance = this.parent_instance
		this.children_instance = instance;
		this.removed_node = document.createComment("yass-if");
		this.prepareParsing();
		for (let child of this.node.childNodes)
			this.children.push(new YassNode(child, this.children_instance));
	}

	prepareParsing()
	{
		if (isComponent(this.node.nodeName))
		{
			this.initial_props_attributes = this.copyAttributes(this.node.attributes);
			this.develop();
		}
		if (this.node.nodeType == Node.TEXT_NODE)
			this.initial_text_content = this.node.textContent;
		this.initial_attributes = this.copyAttributes(this.node.attributes);
	}

	copyAttributes(attributes)
	{
		let ret = [];
		if (attributes != null)
			for (let attribute of attributes)
				ret.push({name: attribute.name, value: attribute.value});
		return ret;
	}

	develop()
	{
		let component = getComponent(this.node.nodeName);
		this.children_instance = new ComponentInstance(component);
		this.children_instance.root_node = this;
		let new_node = document.createElement('div');
		new_node.innerHTML = component.template.trim();
		new_node = new_node.firstChild;
		let parent = this.node.parentNode;
		parent.insertBefore(new_node, this.node);
		this.node.remove();
		this.node = new_node;
		this.instance = this.children_instance;
		this.is_component_root = true;
	}

	addEventListener(event_name, attr_value)
	{
		this.node.addEventListener(event_name, e =>
		{
			this.instance.proxy.$event = e;
			return evalInContext(attr_value, this.instance.proxy);
		})
	}

	parseAttributes(attributes, parse_props = false)
	{
		if (!attributes)
			return ;
		for (let attribute of attributes)
		{
			// Get yass-XXXX instruct or prefix with instruction like '@XXX' ':XXX'
			let instruction = attribute.name.startsWith('yass-') ? attribute.name.substr(5) : attribute.name;
			if (instruction.startsWith('on:') || instruction[0] == '@')
			{
				if (this.render_id == 0)
				{
					let event_name = instruction.includes('@') ? instruction.substr(1) : instruction.substr(3);
					this.addEventListener(event_name, attribute.value);
				}
			}
			else if (instruction == 'if')
			{
				let cond = evalInContext(attribute.value, this.instance.proxy);
				if (cond == false && this.is_removed == false)
				{
					this.node.parentNode.replaceChild(this.removed_node, this.node);
					this.is_removed = true;
				}
				else if (cond == true && this.is_removed == true)
				{
					this.removed_node.parentNode.replaceChild(this.node, this.removed_node);
					this.is_removed = false;
				}
			}
			else if (instruction == 'show')
			{
				if (evalInContext(attribute.value, this.instance.proxy) == false)
					this.node.style.display = 'none';
			}
			else if (instruction.startsWith('bind:') || instruction[0] == ':')
			{
				let new_attribute_name = instruction.split(':')[1];
				
				if (parse_props)
				{
					let new_attribute_value = evalInContext(`(() => {return ${attribute.value}})()`, this.parent_instance.proxy);
					this.instance.props[new_attribute_name] = new_attribute_value;
				}
				else
				{
					// class="XXX" :class="YYY" => class="XXX YYY"
					let new_attribute_value = evalInContext(`(() => {return ${attribute.value}})()`, this.instance.proxy);
					let attribute_value = this.node.getAttribute(new_attribute_name);
					attribute_value = attribute_value + " " + new_attribute_value;
					this.node.setAttribute(new_attribute_name, new_attribute_value);
				}
			}
			else if (instruction == "ref")
				this.instance.proxy.refs[attribute.value] = this.node;
			else if (instruction == "for")
			{
				// console.log(attribute.value);
			}
			else
			{
				if (parse_props)
					this.instance.props[attribute.name] = attribute.value;
			}
		}
	}

	parseText()
	{
		this.node.textContent = this.initial_text_content.replaceAll(/{{([^{][\s\S]+?[^}])}}/g, (match) => evalInContext(match, this.instance.proxy));
	}

	removeAttributes()
	{
		if (this.node.attributes)
			while(this.node.attributes.length > 0)
				this.node.removeAttribute(this.node.attributes[0].name);
	}

	render()
	{
		// evaluate components props and evaluate node attributes
		if (this.is_component_root)
			this.parseAttributes(this.initial_props_attributes, true);
		this.parseAttributes(this.initial_attributes, false);
		
		if (this.node.nodeType == Node.TEXT_NODE)
			this.parseText();
		
		if (!this.is_removed)												// If node is removed, don't render it
			for (let child of this.children)
				child.render();
		if (this.render_id == 0 && this.is_component_root)
			this.instance.callHook('mounted');
		this.render_id++;
	}
}

class Yass
{
	components = [];
	app_container = null;
	virtual_dom = [];

	constructor(id)
	{
		this.components = new Array();
		this.app_container = document.getElementById(id);
		this.initComponents();
	}

	mount()
	{
		console.debug("Mount");
		this.initNodes();
		this.render();
	}

	initNodes()
	{
		for (let child of this.app_container.children)
			this.virtual_dom.push(new YassNode(child, null));
	}

	render()
	{
		// this.app_container.innerHTML = "";
		for (let yass_node of this.virtual_dom)
		{
			if (yass_node.must_render)
				yass_node.render();
				//this.app_container.appendChild(yass_node.node);
		}
	}

	initComponents()
	{
		window.addEventListener('load', async () =>
		{
			await this.loadComponents();
			const event = new Event('yass_components_loaded');
			document.dispatchEvent(event);
		})
		document.addEventListener('yass_components_loaded', e => this.mount('app'));
	}

	async loadComponents()
	{
		return new Promise(async (resolve, reject) =>
		{
			let yass_components = document.getElementsByTagName('YassComponent');
			for (let component of yass_components)
			{
				await fetch(component.getAttribute('link'))
				.then(async res =>
				{
					let text = await res.text();

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
};

let components = [];
function registerComponent(component)
{
	console.debug(`Register component ${component.name}`);
	components.push(new Component(component))
}

function isComponent(name)
{
	return components.find(c => c.name.toLowerCase() == name.toLowerCase()) != undefined;
}

function getComponent(name)
{
	return components.find(c => c.name.toLowerCase() == name.toLowerCase());
}