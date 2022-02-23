class YassStore
{
	name = 'store';
	can_be_injected = true;
	is_reactive = true;

	root_node = null;

	init(root_node)
	{
		this.root_node = root_node;
	}

	addStore(name, store)
	{
		let proxy_handler = {
			get: (target, property) =>
			{
				if (typeof target[property] === 'object' && target[property] !== null
					&& (property[0] != '$' || target[property].is_reactive))
					return new Proxy(target[property], proxy_handler)
				else
					return target[property];
			},
			set: (target, property, value) =>
			{
				Object.defineProperty(target, property, {
					numerable: true,
					configurable: true,
					value: value
				});
				
				if (this.root_node)
					this.root_node.render();
				return true;
			}
		}
		Object.defineProperty(this, name, {
			enumerable: true,
			configurable: true,
			value: new Proxy(store, proxy_handler)
		});
	}
}