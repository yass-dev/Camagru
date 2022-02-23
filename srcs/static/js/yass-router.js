class YassRoute
{
	name = "";

	path = "/";
	component_name = "";
	params = [];
	beforeEnter = () => true;
	
	constructor(name, path, component_name, beforEnter)
	{
		this.name = name;
		this.path = path;
		this.component_name = component_name;
		this.beforeEnter = beforEnter;
	}
}

class YassRouter
{
	name = 'router';
	can_be_injected = true;
	is_reactive = false;

	router_view_component_name = "yass-router-view";
	routes = [];
	router_view_node = null;
	current_component_node = null;
	current_route = null;

	init(root_node)
	{
		let tmp = this.findRouterView(root_node);
		if (tmp)
			this.router_view_node = tmp;

		// For back and forward buttons
		window.onpopstate = e => this.render();
		this.initRouterLinks();
		this.render();
	}

	findRouterView(node)
	{
		if (node.base_node.nodeName.toLowerCase() == this.router_view_component_name.toLowerCase())
			return node;
		for (let child of node.children)
		{
			let router_view = this.findRouterView(child);
			if (router_view)
				return router_view;
		}
		return null;
	}

	addRoute(name, path, component_name, beforeEnter = () => true)
	{
		this.routes.push(new YassRoute(name, path, component_name, beforeEnter));
	}

	findRoute()
	{
		let url = window.location.pathname;
		let url_parts = url.split('/');
		for (let tmp_route of this.routes)
		{
			// Clone to bind parameters value in the route without modifying the original
			let route = JSON.parse(JSON.stringify(tmp_route))
			route['beforeEnter'] = tmp_route.beforeEnter;

			let route_parts = route.path.split('/');
			
			// If they have different numbers of part (/test/abc => 2 parts)
			if (route_parts.length != url_parts.length)
				continue ;

			let match = true;
			for (let i = 0; i < url_parts.length; i++)
			{
				let origin_part = url_parts[i];
				let tmp_part = route_parts[i];

				// If the part of the url is a parameter
				if (tmp_part.startsWith(':'))
				{
					if (origin_part.length == 0)
						match = false;
					else
						route.params[tmp_part.substr(1)] = origin_part;
				}
				// Else if the part is not a parameter and the 2 parts are different
				else if (origin_part != tmp_part)
				{
					match = false;
					break ;
				}
			}

			if (match)
				return route;
		}
		return null;
	}

	push(url)
	{
		history.pushState({}, "", url);
		this.render();
	}

	initRouterLinks()
	{
		document.addEventListener('click', e =>
		{
			let a = e.target.tagName == 'A' ? e.target : e.target.closest('a');
			if (a && a.getAttribute('router-link') !== null)
			{
				e.preventDefault();
				let url = a.getAttribute('href');
				if (!url)
					throw new Error("No href attribute for this router link.");
				this.push(url);
			}
		})
	}

	render()
	{
		let route = this.findRoute();
		if (!route)
			throw new Error("Page not found.")

		if (!route.beforeEnter())
			return ;

		if (this.current_route && route.name == this.current_route.name)
			return ;
		this.current_route = route;
		if (this.current_component_node != null)
		{
			this.current_component_node.component_instance.callHook('unmounted');
			this.current_component_node.remove();
			this.router_view_node.children.splice(0, 1);
		}
		let component_node = document.createElement(route.component_name);
		this.router_view_node.rendered_node.parentNode.insertBefore(component_node, this.router_view_node.rendered_node);
		this.current_component_node = this.router_view_node.addChildren(component_node);

		this.router_view_node.render();
	}
}