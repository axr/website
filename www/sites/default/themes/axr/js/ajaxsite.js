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

			if (/^(#|javascript:)/.test(url))
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
		if (typeof url !== 'string')
		{
			return;
		}

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
		Ajaxsite.template.loading = Ajaxsite.template.loading || {};

		// We already have the template in cache
		if (Ajaxsite.template.cache[name] !== undefined)
		{
			if (typeof callback === 'function')
			{
				callback(Ajaxsite.template.cache[name].template, false);
			}

			return;
		}

		// The template is currently being requested
		if (Ajaxsite.template.loading[name] === true)
		{
			var interval = setInterval(function ()
			{
				if (Ajaxsite.template.cache[name] !== undefined)
				{
					clearInterval(interval);

					if (typeof callback === 'function')
					{
						callback(Ajaxsite.template.cache[name].template, false);
					}
				}
			}, 100);
			return;
		}

		Ajaxsite.template.loading[name] = true;

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
				if (typeof callback == 'function')
				{
					callback('Error loading template', true);
				}

				return;
			}

			Ajaxsite.template.cache[name] = data.payload;
			Ajaxsite.template.loading[name] = false;

			if (typeof callback === 'function')
			{
				callback(data.payload.template, false);
			}
		}).error(function ()
		{
			console.error('Error while loading template ' + name);
		});
	};

	/**
	 * Get info for an url
	 */
	Ajaxsite.urlInfo = function (url, callback)
	{
		var that = this;
		this.cache = this.cache || {};

		if (typeof url !== 'string')
		{
			return;
		}

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
		if (typeof url !== 'string')
		{
			return false;
		}

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
			console.error('Page ' + url + ' cannot be loaded');
			return false;
		}

		Ajaxsite.prepare(url, function ()
		{
			handler(url);
		});

		Ajaxsite.$content.html(Ajaxsite.renderLoading());
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

		if (typeof last_handler === 'object' &&
			typeof last_handler._default === 'function')
		{
			return last_handler._default;
		}
		else if (typeof last_handler === 'function')
		{
			return last_handler;
		}

		return undefined;
	};

	/**
	 * Render a loading page
	 */
	Ajaxsite.renderLoading = function ()
	{
		return 'Loading...';
	};

	/**
	 * A block class
	 */
	Ajaxsite.Block = function ()
	{
		var that = this;

		/**
		 * Generate a random id
		 */
		this.id = ((Math.random()*0xFFFFFF).toString(32) + (Math.random()*0xFFFFFF).toString(32)).replace(/\./g, '');

		/**
		 * HTML for the block
		 */
		this._html = null;

		/**
		 * Replace the placeholder
		 */
		this.html = function (html)
		{
			this._html = html;
			jQuery('.as_block_' + this.id).html(html);
		};

		/**
		 * Return a placeholder
		 */
		this.placeholder = function ()
		{
			var $el = jQuery('<div>').attr('class', 'as_block_' + this.id);

			if (this._html !== null)
			{
				$el.html(this._html);
			}

			return jQuery('<div>').append($el).html();
		};

		/**
		 * Count how many placeholders there are currently in the DOM
		 *
		 * @return int
		 */
		this.count = function ()
		{
			return jQuery('.as_block_' + this.id).length;
		};
	};

	/**
	 * Page handlers
	 */
	Ajaxsite.handlers = {};

	Ajaxsite.handlers['404'] = function (url)
	{
		var le404_block = new Ajaxsite.Block();
		le404_block.html(Ajaxsite.renderLoading());

		Ajaxsite.template('404', function (template, error)
		{
			if (error)
			{
				le404_block.html('404 Not found');
				return;
			}

			le404_block.html(template);
		});

		Ajaxsite.$content.html(le404_block.placeholder());
	};

	/**
	 * Implement search module
	 */
	Ajaxsite.handlers.search = {
		_default: function (url)
		{
			var that = this;

			/**
			 * Search result items' type names
			 */
			this.typeNames = {
				blog: 'blog post',
				page: 'site page',
				wiki: 'wiki page',
				user: 'user'
			};

			/**
			 * What type of content is being searched for
			 */
			this.type = null;

			/**
			 * Search keywords
			 */
			this.keys = null;

			/**
			 * Get search results
			 *
			 * @param string keys
			 * @param function callback
			 */
			this.query = function (callback)
			{
				jQuery.ajax({
					url: '/_ajax/search',
					data: {
						keys: this.keys,
						type: this.type
					},
					dataType: 'json'
				}).success(function (data_raw)
				{
					if (data_raw.status != 0)
					{
						callback([]);
						return;
					}

					var data = [];

					for (var i = 0, c = data_raw.payload.length; i < c; i++)
					{
						var result = data_raw.payload[i];
						var date = new Date(parseInt(result.changed) * 1000);

						result.type_str = that.typeNames[result.type] || result.type;
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

			var match = window.location.pathname
				.match(/^\/search\/([a-z]+)\/(.*)$/);

			if (match === null)
			{
				Ajaxsite.load_url('404');
				return;
			}

			this.type = match[1];
			this.keys = match[2];

			// Create blocks
			var layout_block = new Ajaxsite.Block();
			var options_block = new Ajaxsite.Block();
			var results_block = new Ajaxsite.Block();

			// Insert loading indicators
			layout_block.html(Ajaxsite.renderLoading());
			options_block.html(Ajaxsite.renderLoading());
			results_block.html(Ajaxsite.renderLoading());

			// Preload the results template
			Ajaxsite.template('search_results');

			// Render the main layout
			Ajaxsite.template('search', function (template)
			{
				var html = Mustache.render(template, {
					//options_block: options_block.placeholder(),
					results_block: results_block.placeholder(),
					query: that.keys
				});

				layout_block.html(html);
			});

			// Load options block
			Ajaxsite.template('search_options', function (template)
			{
				var html = Mustache.render(template);
				options_block.html(html);
			});

			// Load search results
			this.query(function (results)
			{
				// Get search results page template
				Ajaxsite.template('search_results', function (template)
				{
					var html = Mustache.render(template, {
						results: results,
						no_results: results.length === 0
					});

					results_block.html(html);
				});
			});

			// Insert layout_block placeholder into #main
			Ajaxsite.$content.html(layout_block.placeholder());
		}
	};

	Ajaxsite.initialize();
})(Ajaxsite);

