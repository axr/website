window.Ajaxsite = {};

(function (Ajaxsite)
{
	Ajaxsite = Ajaxsite || {};

	Ajaxsite.autoloadPaths = Ajaxsite.autoloadPaths || [];
	Ajaxsite.autoloadPaths.push(/^\/search\/node[\/\?$]/);

	/**
	 * The element where AJAX page will be loaded into
	 */
	Ajaxsite.$content = null;

	/**
	 * This must be called before anything can be used
	 */
	Ajaxsite.initialize =  function ()
	{
		Ajaxsite.$content = jQuery('#main');

		var url = window.location.pathname.replace(/^\//, '');

		if (Ajaxsite.autoloadWhenReady === true)
		{
			Ajaxsite.url(url);
		}
		else if (Object.prototype.toString.call(Ajaxsite.autoloadPaths) === '[object Array]')
		{
			for (var i = 0, c = Ajaxsite.autoloadPaths.length; i < c; i++)
			{
				if (Ajaxsite.autoloadPaths[i].test(url))
				{
					Ajaxsite.url(url);
					break;
				}
			}
		}
	};

	/**
	 * Open url
	 *
	 * @param string url
	 */
	Ajaxsite.url = function (url)
	{
		console.log('URL');
		// TODO: Use a history plugin
		return Ajaxsite.load(url);
	};

	/**
	 * Load a template
	 *
	 * @param string name
	 * @param function callback
	 */
	Ajaxsite.template = function (name, callback)
	{
		Ajaxsite.template.cache = Ajaxsite.template.cache || {};

		if (Ajaxsite.template.cache[name] !== undefined)
		{
			callback(Ajaxsite.template.cache[name].template);
			return;
		}

		jQuery.ajax({
			url: '/_ajax/template',
			data: {
				name: name
			},
			dataType: 'json'
		}).success(function (data)
		{
			if (data === null || data.status !== 0)
			{
				// TODO: Handle this more nicely
				alert('An error occurred');
				return;
			}

			if (typeof callback == 'function')
			{
				Ajaxsite.template.cache[name] = data.payload;
				callback(data.payload.template);
			}
		});
	};

	/**
	 * Prepare a url to be loaded
	 */
	Ajaxsite.prepare = function (url)
	{
	};

	/**
	 * Load a page
	 */
	Ajaxsite.load = function (url)
	{
		var handler = this._find_handler(url);

		if (handler === undefined)
		{
			console.log('Page ' + url + ' cannot be loaded');
			return false;
		}

		handler(url);
	};

	/**
	 * Find a handler for supplied url
	 *
	 * @param string url
	 * @return function
	 */
	Ajaxsite._find_handler = function (url)
	{
		var segments = url.split('/');
		var last_handler = this.handlers;

		for (var i = 0, c = segments.length; i < c; i++)
		{
			if (last_handler[segments[i]] === undefined)
			{
				break;
			}

			last_handler = last_handler[segments[i]];
		}

		return (last_handler === this.handlers ||
			typeof last_handler != 'function') ? undefined : last_handler;
	};

	/**
	 * Page handlers
	 */
	Ajaxsite.handlers = {};

	/**
	 * Implement search module
	 */
	Ajaxsite.handlers.search = {
		node: function (url)
		{
			Ajaxsite.template('search_results', function (template)
			{
				Ajaxsite.$content.html(template);
			});
		}
	};

	Ajaxsite.initialize();
})(Ajaxsite);

