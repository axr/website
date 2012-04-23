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
			var url = History.getState().url;

			if (Ajaxsite.load_url(url) === false)
			{
				window.location = url;
			}
		});

		jQuery('a').on('click', function (e)
		{
			var url = jQuery(this).attr('href');

			if (/^\/admin\//.test(url))
			{
				return;
			}

			e.preventDefault();

			Ajaxsite.url(url);
		});

		if (Ajaxsite.autoloadWhenReady === true)
		{
			Ajaxsite.load_url(window.location.pathname);
		}
	};

	/**
	 * Open url
	 *
	 * @param string url
	 */
	Ajaxsite.url = function (url, force)
	{
		url = url.replace(/^https?:\/\/[^\/]+\/(.*)$/, '$1')
			.replace(/^\//, '');

		if ('/' + url === window.location.pathname && force !== true)
		{
			return;
		}

		History.pushState(null, null, '/' + url);
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

		if (false && Ajaxsite.template.cache[name] !== undefined)
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
	 * Get info for an url
	 */
	Ajaxsite.urlInfo = function (url, callback)
	{
		var that = this;
		this.cache = this.cache || {};

		if (this.cache[url] === undefined)
		{
			jQuery.ajax({
				url: '/_ajax/info',
				data: {
					url: url
				},
				dataType: 'json'
			}).success(function (data)
			{
				that.cache[url] = data.payload;

				if (typeof callback === 'function')
				{
					callback(that.cache[url]);
				}
			}).error(function (data)
			{
				that.cache[url] = null;

				if (typeof callback === 'function')
				{
					callback(that.cache[url]);
				}
			});
		}
		else
		{
			if (typeof callback === 'function')
			{
				callback(this.cache[url]);
			}
		}
	};

	/**
	 * Load dependencies for the page
	 */
	Ajaxsite.prepare = function (info, callback)
	{
		if (typeof info === 'string')
		{
			Ajaxsite.urlInfo(info, function (info)
			{
				Ajaxsite.prepare(info, callback);
			});

			return;
		}

		if (this.loadedCssEl !== undefined)
		{
			// Unload previously loaded CSS
			for (var i = 0, c = this.loadedCssEl.length; i < c; i++)
			{
				this.loadedCssEl[i].remove();
			}
		}

		// In future there will be a way to unload JS, too
		// But right now I don't want to over complicate things

		// Loaded CSS elements go there so they can be removed later
		this.loadedCssEl = [];

		// Load CSS
		for (var i = 0, c = info.css.length; i < c; i++)
		{
			var link = jQuery('<link>')
				.attr('type', 'text/css')
				.attr('rel', 'stylesheet')
				.attr('href', info.css[i]);

			jQuery('head').append(link);
			this.loadedCssEl.push(link);
		}

		// Load JS
		for (var i = 0, c = info.js.length; i < c; i++)
		{
			var script = jQuery('<script>').attr('src', info.js[i]);
		}

		if (typeof callback === 'function')
		{
			callback();
		}
	};

	/**
	 * Load an URL
	 *
	 * @param string url
	 */
	Ajaxsite.load_url = function (url)
	{
		url = url.replace(/^https?:\/\/[^\/]+\/(.*)$/, '$1')
			.replace(/^\//, '');

		return Ajaxsite.load(url);
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

		Ajaxsite.prepare(url, function ()
		{
			handler(url);
		});
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
	 * Render a loading page
	 */
	Ajaxsite.renderLoading = function ()
	{
		return 'Loading';
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
			 * Get search results
			 *
			 * @param string keys
			 * @param function callback
			 */
			this.query = function (keys, callback)
			{
				jQuery.ajax({
					url: '/_ajax/search',
					data: {
						keys: keys
					},
					dataType: 'json'
				}).success(function (data_raw)
				{
					var data = [];

					for (var i = 0, c = data_raw.payload.length; i < c; i++)
					{
						var result = data_raw.payload[i];
						var date = new Date(parseInt(result.changed) * 1000);

						result.date = date.getFullYear() + '/' +
							date.getMonth() + '/' + date.getDate();
						result.time = date.getHours() + ':' + date.getMinutes();

						data.push(result);
					}

					if (typeof callback === 'function')
					{
						callback(data);
					}
				}).error(function ()
				{
					if (typeof callback === 'function')
					{
						callback([]);
					}
				});
			};

			var keys = decodeURIComponent(window.location.pathname
				.replace(/^\/search\/node\/(.*)$/, '$1'));

			this.query(keys, function (results)
			{
				that.render({
					results: results,
					query: keys,
					no_results: results.length === 0
				});
			});

			Ajaxsite.$content.html(Ajaxsite.renderLoading());
		}
	};

	Ajaxsite.initialize();
})(Ajaxsite);

