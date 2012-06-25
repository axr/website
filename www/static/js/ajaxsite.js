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
			var state = History.getState();

			state.url = state.url.replace(/^https?:\/\/[^\/]+(\/.*)$/, '$1');

			if (!/^\/search\//.test(state.url) ||
				/^(https?:|#|javascript:)/.test(state.url))
			{
				return;
			}


			if (Ajaxsite.load_url(state.url, state.data) === false)
			{
				window.location = state.url;
			}
		});

		jQuery('a').on('click', function (e)
		{
			var url = jQuery(this).attr('href');

			if (!/^\/search\//.test(url) ||
				e.which !== 1 || /^(https?:|#|javascript:)/.test(url))
			{
				return;
			}

			e.preventDefault();

			Ajaxsite.url(url);
		});

		if (typeof Ajaxsite_onInit === 'object' &&
			Ajaxsite_onInit instanceof Array)
		{
			for (var i = 0, c = Ajaxsite_onInit.length; i < c; i++)
			{
				if (typeof Ajaxsite_onInit[i] === 'function')
				{
					Ajaxsite_onInit[i].call(null);
				}
			}
		}

		if (Ajaxsite.autoloadWhenReady === true)
		{
			Ajaxsite.load_url(window.location.pathname);
		}
	};


	/**
	 * Call a callback when ajaxsite has initialized
	 *
	 * @param function callback
	 */
	Ajaxsite.on_init = function (callback)
	{
		if (typeof callback === 'function')
		{
			callback();
		}
	};

	/**
	 * Open url
	 *
	 * @param string url
	 * @param bool force
	 * @param Object data
	 */
	Ajaxsite.url = function (url, force, data)
	{
		if (typeof url !== 'string')
		{
			return;
		}

		url = url.replace(/^https?:\/\/[^\/]+(\/.*)$/, '$1');
		url = (url[0] !== '/') ? '/' + url : url;

		if (url === window.location.pathname && force !== true)
		{
			return;
		}

		History.pushState(data || null, null, url);
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
		Ajaxsite.urlInfo.cache = Ajaxsite.urlInfo.cache || {};

		if (typeof url !== 'string')
		{
			return;
		}

		if (Ajaxsite.urlInfo.cache[url] === undefined)
		{
			jQuery.ajax({
				url: '/_ajax/info',
				data: {
					url: url
				},
				dataType: 'json'
			}).success(function (data)
			{
				Ajaxsite.urlInfo.cache[url] = data.payload;

				if (typeof callback === 'function')
				{
					callback(Ajaxsite.urlInfo.cache[url]);
				}
			}).error(function (data)
			{
				Ajaxsite.urlInfo.cache[url] = null;

				if (typeof callback === 'function')
				{
					callback(Ajaxsite.urlInfo.cache[url]);
				}
			});
		}
		else
		{
			if (typeof callback === 'function')
			{
				callback(Ajaxsite.urlInfo.cache[url]);
			}
		}
	};

	/**
	 * Load an URL
	 *
	 * @param string url
	 * @param Object data
	 */
	Ajaxsite.load_url = function (url, data)
	{
		if (typeof url !== 'string')
		{
			return false;
		}

		url = url.replace(/^https?:\/\/[^\/]+\/(.*)$/, '$1')
			.replace(/^\//, '');

		return Ajaxsite.load(url, data || undefined);
	};

	/**
	 * Load a page
	 */
	Ajaxsite.load = function (url, data)
	{
		var handler = this._find_handler(url);

		if (handler === undefined)
		{
			console.error('Page ' + url + ' cannot be loaded');
			return false;
		}

		Ajaxsite.$content.html(Ajaxsite.renderLoading());

		handler(url, data || undefined);
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
	Ajaxsite.Block = function (id)
	{
		var that = this;

		/**
		 * Generate a random id
		 */
		this.id = (id !== undefined) ? id : ((Math.random()*0xFFFFFF).toString(32) + (Math.random()*0xFFFFFF).toString(32)).replace(/\./g, '');

		/**
		 * HTML for the block
		 */
		this._html = null;

		/**
		 * Fields
		 */
		this._fields = {};

		/**
		 * Replace the placeholder
		 */
		this.html = function (html)
		{
			this._html = html;
			jQuery('.as_block_' + this.id).html(html);
		};

		/**
		 * Set value for a field
		 *
		 * @param string name
		 * @param string value
		 */
		this.setField = function (name, value)
		{
			value = value
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/"/g, '&quot;');

			this._fields[name] = value;
			jQuery('.as_field_' + this.id + '_' + name).html(value);
		};

		/**
		 * Get HTML
		 */
		this.getHtml = function ()
		{
			return this._html;
		};

		/**
		 * Return a placeholder
		 */
		this.placeholder = function ()
		{
			var $el = jQuery('<div>')
				.attr('class', 'as_block_' + this.id)
				.html(this._html);

			return jQuery('<div>').append($el).html();
		};

		/**
		 * Return placeholder for a field
		 *
		 * @param string name
		 * @param string value default value
		 * @return string
		 */
		this.field = function (name, value)
		{
			var $el = jQuery('<span>')
				.attr('class', 'as_field_' + this.id + '_' + name)
				.html(this._fields[name] || value || '');

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

		// Wait for element to be created
		var interval = setInterval(function ()
		{
			if (jQuery('.as_block_' + that.id).length > 0)
			{
				clearInterval(interval);
				jQuery('.as_block_' + that.id).html(that._html);
			}
		}, 80);
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
		_default: function (url, data)
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
			 * Search types
			 */
			this.searchTypes = {
				'mixed': {name: 'Everything'},
				'node': {name: 'Site pages'},
				'wiki': {name: 'Wiki pages'},
				'user': {name: 'Users'}
			};

			/**
			 * Is initialized
			 */
			this.isInitialized = this.isInitialized || false;

			/**
			 * History data
			 */
			this.data = data || {};

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

						if (!isNaN(result.changed))
						{
							var date = new Date(parseInt(result.changed) * 1000);
							result.date = date.getFullYear() + '/' +
								date.getMonth() + '/' + date.getDate() +
								'-' + date.getHours() + ':' + date.getMinutes();
						}

						result.type_str = that.typeNames[result.type] ||
							result.type;

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
					options_block: options_block.placeholder(),
					results_block: results_block.placeholder(),
					query_field: layout_block.field('query', that.keys)
				});

				layout_block.html(html);
			});

			// Load options block
			Ajaxsite.template('search_options', function (template)
			{
				var types = [];

				for (var type in that.searchTypes)
				{
					types.push({
						type: type,
						name: that.searchTypes[type].name,
						selected: type === that.type
					});
				}

				var html = Mustache.render(template, {
					types: types,
					query: that.keys
				});

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

			if (!this.isInitialized)
			{
				var $bar = undefined;
				var barOffset = undefined;
				var barOffsetDiff = 7;

				jQuery(window).scroll(function (e)
				{
					$bar = $bar || jQuery('#search .search_options');

					if ($bar.length == 0)
					{
						$bar = undefined;
						return;
					}

					barOffset = barOffset || $bar.offset().top;

					if (jQuery(window).scrollTop() > barOffset - barOffsetDiff)
					{
						$bar.addClass('stick');

						if (jQuery('#toolbar').length > 0)
						{
							barOffsetDiff = 40;
							$bar.addClass('stickLess');
						}
					}
					else
					{
						$bar.removeClass('stick stickLess');
					}
				});

				jQuery('#main').on('change',
					'#search .search_options select[name=type]',
					function (e)
				{
					e.preventDefault();

					var type = jQuery(this).val();

					Ajaxsite.url('search/' + type + '/' +
						encodeURIComponent(that.keys));
				});

				jQuery('#main').on('submit',
					'#search .search_options form.search',
					function (e)
				{
					e.preventDefault();

					var keys = jQuery(this).find('input[type=search]').val();
					var url = '/search/' + that.type + '/' +
						encodeURIComponent(keys);

					Ajaxsite.url(url, false);
				});
			}

			// Insert layout_block placeholder into #main
			Ajaxsite.$content.html(layout_block.placeholder());

			this.isInitialized = true;
		}
	};

	Ajaxsite.initialize();
})(Ajaxsite);

