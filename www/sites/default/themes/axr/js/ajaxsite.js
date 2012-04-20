window.Ajaxsite = window.Ajaxsite || {};

(function (Ajaxsite)
{
	Ajaxsite = Ajaxsite || {};

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

		History.Adapter.bind(window, 'statechange', function ()
		{
			Ajaxsite.url(History.getState().url, false);
		});

		jQuery('a').on('click', function (e)
		{
			var url = $(this).attr('href');

			if (/^\/admin\//.test(url))
			{
				return;
			}

			e.preventDefault();

			if (Ajaxsite.url(url) === false)
			{
				window.location = url;
			}
		});

		if (Ajaxsite.autoloadWhenReady === true)
		{
			Ajaxsite.url(window.location.pathname, false, true);
		}
	};

	/**
	 * Open url
	 *
	 * @param string url
	 */
	Ajaxsite.url = function (url, update_history, force)
	{
		url = url.replace(/^https?:\/\/[^\/]+\/(.*)$/, '$1')
			.replace(/^\//, '');

		if ('/' + url === window.location.pathname && force !== true)
		{
			return;
		}

		if (update_history !== false)
		{
			History.pushState(null, null, '/' + url);
		}

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

				if (typeof callback == 'function')
				{
					callback(null);
				}

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
			var that = this;

			/**
			 * Render the page
			 */
			this.render = function (view)
			{
				Ajaxsite.template('search_results', function (template)
				{
					var html = Mustache.render(template, view);
					Ajaxsite.$content.html(html);
				});
			};

			/**
			 * Get search results from Drupal
			 *
			 * @param string keys
			 * @param function callback
			 */
			this.query_drupal = function (keys, callback)
			{
				jQuery.ajax({
					url: '/_ajax/search',
					data: {
						keys: keys
					},
					dataType: 'json'
				}).success(function (data)
				{
					if (typeof callback === 'function')
					{
						callback(data.payload);
					}
				}).error(function ()
				{
					if (typeof callback === 'function')
					{
						callback([]);
					}
				});
			};

			/**
			 * Get search results from wiki
			 *
			 * @param string query
			 * @param function callback
			 */
			this.query_mw = function (query, callback)
			{
				if (typeof callback === 'function')
				{
					callback([]);
				}
			};

			var keys = window.location.pathname
				.replace(/^\/search\/node\/(.*)$/, '$1');
			var finished = 0;
			var results = [];

			this.query_drupal(keys, function (data)
			{
				results = results.concat(data);
				finished++;
			});

			this.query_mw(keys, function (data)
			{
				results = results.concat(data);
				finished++;
			});

			// This weirdness allows the code to look better
			var interval = setInterval(function ()
			{
				if (finished < 2)
				{
					return;
				}

				clearInterval(interval);

				this.render({
					results: results,
					query: keys,
					no_results: results.length === 0
				});
			}, 50);
		}
	};

	Ajaxsite.initialize();
})(Ajaxsite);

