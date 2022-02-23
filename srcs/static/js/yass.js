class Component
{
	name = "";
	props = {};
	data = () => ({});
	mehtods = {};
	mounted = () => {};
	unmounted = () => {};
	updated = () => {};

	template = "";

	constructor({name, props, data, methods, mounted, updated, unmounted})
	{
		this.name = name;
		this.props = props ? props : {},
		this.data = data ? data : () => ({});
		this.methods = methods ? methods : {};
		this.mounted = mounted ? mounted : () => {};
		this.unmounted = unmounted ? unmounted : () => {};
		this.updated = updated ? updated : () => {};
		this.template = document.querySelector(`template#${name}`).innerHTML;
	}
}

class ComponentInstance
{
	slot = "";
	data = {};
	props = {};
	refs = {};
	root_node = null;
	data_proxy = null;
	props_proxy = null;
	proxy = null;
	sub_proxy_handler = {};
	proxy_data = {};
	is_mounted = false;
	proxy_handler = {};
	events = [];
	parent_instance = null;

	constructor(component, parent_instance = null)
	{
		this.component = component;
		this.parent_instance = parent_instance
		this.sub_proxy_handler =
		{
			get: (target, property) =>
			{
				return target[property];
			},
			set: (target, property, value) =>
			{
				target[property] = value;
				this.updatePropsInProxy();
				return true
			}
		}

		this.proxy_handler = {
			get: (target, property) =>
			{
				if (typeof target[property] === 'object' && target[property] !== null
					&& (property[0] != '$'))
					return new Proxy(target[property], this.proxy_handler)
				else
					return target[property];
			},
			set: (target, property, value) =>
			{	
				target[property] = value;
				if (!(property in this.props_proxy))
					this.root_node.render();
				return true;
			}
		}
	}

	updatePropsInProxy()
	{
		for (let key of Object.keys(this.props))
			this.proxy[key] = this.props[key];
	}

	createProxy(proxy_injections)
	{
		this.props_proxy = new Proxy(this.props, this.sub_proxy_handler);

		this.proxy = new Proxy({
			name: this.component.name,
			...this.props,
			...this.data,
			...this.component.methods,
			...proxy_injections,
			$refs: [],
			mounted: this.component.mounted,
			updated: this.component.updated,
			unmounted: this.component.unmounted,
			instance: this,
			parent_instance: this.parent_instance,
			$emit(event_name, e)
			{
				this.parent_instance.callEvent(event_name, e);
			}
		}, this.proxy_handler);
	}

	callHook(name)
	{
		if (name == 'mounted')
		{
			let new_proxy_data = {};
			
			Object.assign(new_proxy_data, this.proxy, this.component.data.call(this.proxy));
	
			this.proxy = new Proxy(new_proxy_data, this.proxy_handler);
			this.is_mounted = true;
		}
		else if (name == "unmounted" && !this.is_mounted)
			return ;
		try
		{
			this.proxy[name].call(this.proxy);
		}
		catch (e)
		{
			console.log("Error on call", name, this.proxy[name], this.proxy);
			console.error(e);
		}
	}

	addEventListener(event_name, func, custom_variables)
	{
		this.events.push({
			name: event_name,
			func: func,
			custom_variables: custom_variables
		});
	}

	callEvent(event_name, e)
	{
		let listener = this.events.find(ev => ev.name == event_name);
		if (listener)
		{
			evalInContextWithCustomVariables(listener.func, this.proxy, listener.custom_variables);
		}
		else
			console.warn(`Event ${event_name} called but listener not found in ${this.component.name}.`);
	}
}

class YassNode
{
	base_node = null;
	developed_node = null;
	rendered_node = null;
	comment_node = null;
	parent_node = null;

	is_component = false;
	component_instance = null;
	parent_component_instance = null;

	children = [];

	initial_components_attributes = [];
	initial_attributes = [];

	initial_text_content = "";

	is_rendered = false;

	is_for_loop = false;
	for_children = [];

	custom_variables = [];
	proxy_injections = {};

	default_display_value = null;

	// For yass-for node 
	array = [];

	render_id = 0;

	constructor(node, component_instance, custom_variables, proxy_injections)
	{
		this.base_node = node;
		this.developed_node = node;
		this.comment_node = new Comment("YASSSS Comment");
		this.parent_node = node.parentNode;

		if (custom_variables === undefined || proxy_injections === undefined)
			throw new Error("Missing parameters in YassNode constructor");
		
		this.component_instance = component_instance;
		this.parent_component_instance = component_instance;

		// Clone
		this.custom_variables = clone(custom_variables);

		this.proxy_injections = proxy_injections;

		if (isComponent(this.base_node.nodeName))
		{
			this.is_component = true;
			this.initial_components_attributes = copyAttributes(this.base_node);
			this.is_for_loop = this.isForLoop();
			if (!this.is_for_loop)
				this.develop();
		}

		if (this.developed_node.nodeType == Node.TEXT_NODE)
			this.initial_text_content = this.developed_node.textContent;

		this.initial_attributes = copyAttributes(this.developed_node);
		this.is_for_loop = this.isForLoop();

		this.rendered_node = this.developed_node;

		if (this.is_for_loop)
		{
			this.comment_node.data = "yass-for";
			this.parent_node.replaceChild(this.comment_node, this.rendered_node);
			this.rendered_node = this.comment_node;
		}

		else
		{
			this.parseEvents();
			for (let child of this.developed_node.childNodes)
				this.children.push(new YassNode(child, this.component_instance, this.custom_variables, this.proxy_injections));
		}
	}

	develop()
	{
		let component = getComponent(this.base_node.nodeName);
		this.is_component = true;
		this.component_instance = new ComponentInstance(component, this.component_instance);
		this.component_instance.root_node = this;
		this.component_instance.createProxy(this.proxy_injections);
		this.component_instance.root_node = this;
		let tmp_node = document.createElement("div");
		tmp_node.innerHTML = component.template.trim();
		this.developed_node = tmp_node.firstChild;
		this.parent_node.replaceChild(this.developed_node, this.base_node);
	}

	render()
	{
		let node_to_render = null;
		if (this.evaluateCondition() && !this.is_for_loop)
		{
			node_to_render = this.developed_node;

			if (this.is_component)
				this.parseAttributes(true);
			this.parseAttributes(false);
			
			if (this.developed_node.nodeType == Node.TEXT_NODE)
				this.parseText();

			if (this.render_id == 0)
			{
				if (this.is_component && this.component_instance.is_mounted == false)
					this.component_instance.callHook('mounted');
				this.is_rendered = true;
			}
			else
			{
				if (this.is_component)
					this.component_instance.callHook('updated');
			}

			for (let child of this.children)
				child.render();

			this.render_id++;
		}
		else if (this.is_for_loop)
		{
			node_to_render = this.comment_node;
			this.generateForLoopChildren();
			for (let child of this.for_children)
				child.render();
		}
		else
		{
			this.comment_node.data = "yass-if";
			node_to_render = this.comment_node;
			this.is_rendered = true;
			if (this.is_component)
				this.component_instance.callHook('unmounted')
		}

		try
		{
			if (!node_to_render.isEqualNode(this.rendered_node))
			{
				this.parent_node.replaceChild(node_to_render, this.rendered_node);
				this.rendered_node = node_to_render;
			}
		}
		catch(e)
		{
			console.log("Failed to replace child", this.parent_node, this.rendered_node, "by", node_to_render);
			console.log(e);
			throw new Error;
		}
	}

	evaluateCondition()
	{
		if (this.is_component)
		{
			for (let attribute of this.initial_components_attributes)
			{
				if (attribute.name == "yass-if")
				{
					if (!evalInContextWithCustomVariables(`(() => {return ${attribute.value}})()`, this.parent_component_instance.proxy, this.custom_variables))
						return false;
				}
			}
		}
		for (let attribute of this.initial_attributes)
		{
			if (attribute.name == "yass-if")
				return evalInContextWithCustomVariables(`(() => {return ${attribute.value}})()`, this.component_instance.proxy, this.custom_variables);
		}
		return true;
	}

	isForLoop()
	{
		if (this.is_component)
			return this.initial_components_attributes.find(attr => attr.name == "yass-for") != undefined;
		else
			return this.initial_attributes.find(attr => attr.name == "yass-for") != undefined;
	}

	generateForLoopChildren()
	{
		let attributes = (this.is_component ? this.initial_components_attributes : this.initial_attributes);
		let context = this.parent_component_instance.proxy;
		let for_attr = attributes.find(attr => attr.name == 'yass-for');
		let parts = for_attr.value.trim().split('in').map(str => str.trim());
		let var_name = parts[0];
		let array_name = parts[1];
		let ex_arr = clone(this.array);
		let arr = evalInContextWithCustomVariables(array_name, context, this.custom_variables);
		// console.log(`Compare ${array_name}`, {a: clone(arr), b: clone(ex_arr)}, isEqual(arr, ex_arr));
		if (isEqual(ex_arr, arr))
			return ;
		this.array = clone(arr);
		this.removeForLoopChildren();	// Recreate children
		let i = 0;
		for (let item of arr)
		{
			let new_elem = this.base_node.cloneNode(true);
			new_elem.removeAttribute('yass-for');
			this.parent_node.insertBefore(new_elem, this.rendered_node);
			let variables = clone(this.custom_variables);
			variables.push({name: var_name, value: item});
			variables.push({name: 'for_index', value: i});
			let new_yass_node = new YassNode(new_elem, this.component_instance, variables, this.proxy_injections);
			this.for_children.push(new_yass_node);
			i++;
		}
	}

	removeForLoopChildren()
	{
		for (let i = 0; i < this.for_children.length;)
		{
			this.for_children[0].rendered_node.remove();
			this.for_children.splice(0, 1);
		}
	}

	parseEvents()
	{
		// Handle for component event ?
		if (this.is_component)
		{
			for (let attribute of this.initial_components_attributes)
			{
				let instruction = attribute.name.startsWith('yass-') ? attribute.name.substr(5) : attribute.name;
				if (instruction.startsWith('on:') || instruction[0] == '@')
				{
					let event_name = instruction.includes('@') ? instruction.substr(1) : instruction.substr(3);
					this.parent_component_instance.addEventListener(event_name, attribute.value, this.custom_variables);
				}
			}
		}
		for (let attribute of this.initial_attributes)
		{
			let instruction = attribute.name.startsWith('yass-') ? attribute.name.substr(5) : attribute.name;
			if (instruction.startsWith('on:') || instruction[0] == '@')
			{
				let event_name = instruction.includes('@') ? instruction.substr(1) : instruction.substr(3);
				this.addEventListener(event_name, attribute.value);
			}
		}
	}

	addEventListener(event_name, func)
	{
		this.developed_node.addEventListener(event_name, e =>
		{
			// this.custom_variables.push(event_var);
			this.setCustomVariable('$event', e);
			evalInContextWithCustomVariables(func, this.component_instance.proxy, this.custom_variables);
			// this.removeCustomVariable('$event');
		})
	}

	parseText()
	{
		this.developed_node.textContent = this.initial_text_content.replaceAll(/{{([^{][\s\S]+?[^}])}}/g, (match) => evalInContextWithCustomVariables(match, this.component_instance.proxy, this.custom_variables));
	}

	parseAttributes(parse_props)
	{
		let attributes = (parse_props ? this.initial_components_attributes : this.initial_attributes);
		for (let attribute of attributes)
		{
			if (attribute.name == 'yass-show')
			{
				if (evalInContextWithCustomVariables(attribute.value, this.component_instance.proxy, this.custom_variables) == false)
				{
					if (this.default_display_value === null)
						this.default_display_value = this.developed_node.style.display;
					this.developed_node.style.display = 'none';
				}
				else
					this.developed_node.style.display = this.default_display_value;
			}
			else if (attribute.name.startsWith('yass-bind:') || attribute.name[0] == ':')
			{
				let new_attribute_name = attribute.name.split(':')[1];
				
				if (parse_props)
				{
					let new_attribute_value = evalInContextWithCustomVariables(`(() => {return ${attribute.value}})()`, this.parent_component_instance.proxy, this.custom_variables);
					this.component_instance.props_proxy[new_attribute_name] = new_attribute_value;
				}
				else
				{
					// class="XXX" :class="YYY" => class="XXX YYY"
					let new_attribute_value = evalInContextWithCustomVariables(`(() => {return ${attribute.value}})()`, this.component_instance.proxy, this.custom_variables);
					if (new_attribute_name == "class")
					{
						let base_classes = (this.developed_node.getAttribute(new_attribute_name) || "").split(' ');
						let binded_classes = [];
						for (let key in new_attribute_value)
						{
							if (new_attribute_value[key])
								binded_classes.push(key);
							else
							{
								let i = base_classes.findIndex(c => c == key);
								if (i != -1)
									base_classes.splice(i, 1);
							}
						}
						let classes = base_classes.concat(binded_classes);
						new_attribute_value = classes.join(' ');
					}
					else if (new_attribute_name == "style")
					{
						// console.log("Bind style ", new_attribute_value, "on ", this.developed_node);
						// For each rule in binded css
						for (let rule in new_attribute_value)
							this.developed_node.style[rule] = new_attribute_value[rule];
						continue ;
					}
					this.developed_node.setAttribute(new_attribute_name, new_attribute_value);
				}
			}
			else if (attribute.name == "ref")
				this.component_instance.proxy.$refs[attribute.value] = this.developed_node;
			else if (!isSpecialAttribute(attribute.name))
			{
				if (parse_props)
					this.component_instance.props_proxy[attribute.name] = attribute.value;
			}
		}
	}

	setCustomVariable(name, value)
	{
		let v = this.custom_variables.find(variable => variable.name == name);
		if (v == undefined)
			this.custom_variables.push({name: name, value: value});
		else
			v.value = value;

		for (let child of this.children)
			child.setCustomVariable(name, value);
	}

	removeCustomVariable(name)
	{
		let index = this.custom_variables.findIndex(va => va.name == name);
		if (index != -1)
			this.custom_variables.splice(index, 1);

		for (let child of this.children)
			child.removeCustomVariable(name);
	}

	remove()
	{
		this.rendered_node.remove();
		this.developed_node.remove();
		this.base_node.remove();
	}

	addChildren(dom_node)
	{
		let new_node = new YassNode(dom_node, this.component_instance, this.custom_variables, this.proxy_injections);
		this.children.push(new_node);
		return new_node;
	}
}

class Yass
{
	components = [];
	app_container = null;
	root_node = null;
	plugins = [];

	constructor(id, plugins = [])
	{
		this.components = new Array();
		this.app_container = document.getElementById(id);

		for (let plugin of plugins)
		{
			let plugin_instance = new plugin();
			this.plugins[plugin_instance.name] = plugin_instance;
		}

		this.loadComponents().then(() =>
		{
			this.mount()
		});
	}

	mount()
	{
		this.initNodes();
		this.render();
		for (let plugin_key in this.plugins)
			this.plugins[plugin_key].init(this.root_node);
	}

	initNodes()
	{
		let plugins_proxy_injection = {};
		for (let plugin_key in this.plugins)
		{
			let plugin = this.plugins[plugin_key];
			if (plugin.can_be_injected)
				plugins_proxy_injection['$' + plugin_key] = plugin;
		}

		
		this.root_node = new YassNode(this.app_container.children[0], null, [], {...plugins_proxy_injection});

		window['plugins'] = {...plugins_proxy_injection};
	}

	render()
	{
		this.root_node.render();
	}

	initComponents()
	{
		window.addEventListener('load', async () =>
		{
			await this.loadComponents();
			const event = new Event('yass_components_loaded');
			document.dispatchEvent(event);
		})
		document.addEventListener('yass_components_loaded', e => this.mount());
	}

	async loadComponents()
	{
		return new Promise(async resolve =>
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
	components.push(new Component(component));
}

function isComponent(name)
{
	return components.find(c => c.name.toLowerCase() == name.toLowerCase()) != undefined;
}

function getComponent(name)
{
	return components.find(c => c.name.toLowerCase() == name.toLowerCase());
}

function copyAttributes(node)
{
	let ret = [];
	if (!node.attributes)
		return ret;
	for (let attribute of node.attributes)
		ret.push({name: attribute.name, value: attribute.value});
	return ret;
}

function evalInContext(str, context)
{
	return function()
	{
		return eval(str);
	}.call(context);
}

function evalInContextWithCustomVariables(str, context, custom_variables)
{
	return function()
	{
		for (let variable of custom_variables)
			eval(`${variable.name} = variable.value`);
		let ret = eval(str);
		for (let variable of custom_variables)
			eval(`delete ${variable.name}`);
		return ret;
	}.call(context);
}

function isSpecialAttribute(name)
{
	return (name[0] == ':' || name[0] == '@' || name.startsWith('yass-'))
}

function clone(arr)
{
	return JSON.parse(JSON.stringify(arr));
}

function isEqual(a, b)
{
	return JSON.stringify(a) == JSON.stringify(b);
}