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
		Ajaxsite.content = new Ajaxsite.Block();
		Ajaxsite.content.html(jQuery('#main').html());
		jQuery('#main').html(Ajaxsite.content.placeholder());

		History.Adapter.bind(window, 'statechange', function ()
		{
			var state = History.getState();
			var path = state.url.replace(/^https?:\/\/[^\/]+(\/.*)?$/, '$1');
			path = (path === '') ? '/' : path;

			if (state.data._believeMe !== true)
			{
				window.location = path;
				return;
			}

			if (Ajaxsite.load_url(path, state.data) === false)
			{
				window.location = path;
			}
		});

		jQuery('a').on('click', function (e)
		{
			var url = jQuery(this).attr('href');
			var host = window.location.host;

			if (/^https?/.test(url))
			{
				host = url.replace(/^https?:\/\/([^\/]+)(\/?.*)$/, '$1');
			}

			// Exceptions
			if (e.which !== 1 || host !== window.location.host ||
				/(#|^javascript:)/.test(url))
			{
				return;
			}

			e.preventDefault();
			Ajaxsite.url(url);
		});

		Ajaxsite.rsrc.initialize();

		// Execute callbacks
		if (typeof Ajaxsite_onInit === 'object' &&
			Ajaxsite_onInit instanceof Array)
		{
			for (var i = 0, c = Ajaxsite_onInit.length; i < c; i++)
			{
				Ajaxsite.on_init(Ajaxsite_onInit[i]);
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

		data = data || {};
		data._believeMe = true;

		if (url === window.location.pathname && force !== true)
		{
			return;
		}

		History.pushState(data, null, url);
	};

	/**
	 * Router
	 */
	Ajaxsite.router = {
		_routes: [
			{
				regex: /^\/get-involved\/?$/,
				handler: function (data)
				{
					Ajaxsite.handlers.get_involved(data);
				},
				rsrc_bundles: [
					'css/get_involved.css',
					'js/get_involved.js'
				]
			},
			{
				regex: /^\/$/,
				handler: function (data)
				{
					//Ajaxsite.handlers.home(data);
					window.location = '/';
				},
				rsrc_bundles: [
					'css/home.css'
				]
			}
		],

		/**
		 * Route the request (find a handler)
		 *
		 * @param string url
		 * @return object
		 */
		find: function (url)
		{
			for (var i = 0, c = this._routes.length; i < c; i++)
			{
				var route = this._routes[i];

				if (!route.regex.test(url))
				{
					continue;
				}

				return {
					run_handler: route.handler,
					rsrc_bundles: route.rsrc_bundles || []
				};
			}
		}
	};

	/**
	 * Cache system
	 */
	Ajaxsite.cache = {
		_cache: {},

		/**
		 * Save data into cache
		 *
		 * @param string key
		 * @param mixed
		 */
		set: function (key, data)
		{
			this._cache[key] = data;
		},

		/**
		 * Get data from cache
		 *
		 * @param string key
		 * @return mixed
		 */
		get: function (key)
		{
			return this._cache[key] || undefined;
		},

		/**
		 * Remove an item from the cache
		 *
		 * @param string key
		 */
		rm: function (key)
		{
			delete this._cache[key];
		}
	};

	/**
	 * Functions that deal with fetching data
	 */
	Ajaxsite.data = {};

	/**
	 * Load a template
	 *
	 * @param string name
	 * @param function callback(template, error)
	 */
	Ajaxsite.data.template = function (name, callback)
	{
		if (typeof callback !== 'function')
		{
			callback = function () {};
		}

		// We already have the template in cache
		if (Ajaxsite.cache.get('/template/' + name) !== undefined)
		{
			callback(Ajaxsite.cache.get('/template/' + name), null);
			return;
		}

		// The template is currently being requested
		if (Ajaxsite.cache.get('/template/:loading/' + name) === true)
		{
			var interval = setInterval(function ()
			{
				if (Ajaxsite.cache.get('/template/' + name) !== undefined)
				{
					clearInterval(interval);
					callback(Ajaxsite.cache.get('/template/' + name), null);
				}
			}, 100);

			return;
		}

		Ajaxsite.cache.get('/template/:loading/' + name, true);

		jQuery.ajax({
			url: '/_ajax/template',
			data: {
				name: name
			},
			dataType: 'json'
		}).success(function (data)
		{
			if (typeof data !== 'object' || data.status !== 0)
			{
				callback(null, 'Error loading template');

				return;
			}

			Ajaxsite.cache.get('/template/' + name, data.payload.template);
			Ajaxsite.cache.get('/template/:loading/' + name, false);

			callback(data.payload.template, false);
		}).error(function ()
		{
			console.error('Error while loading template ' + name);
			callback(null, 'Error loading template');
		});
	};

	/**
	 * Get a prerendered page that supports _forajax querystring parameter
	 * There is no automatic cache since this data should not be cached.
	 *
	 * @param string path
	 * @param function callback(data, error)
	 */
	Ajaxsite.data.sp_forajax = function (path, callback)
	{
		jQuery.ajax({
			url: path,
			data: {
				_forajax: 1
			},
			method: 'GET',
			dataType: 'json',
			success: function (data)
			{
				if (typeof data !== 'object' || data.status !== 0)
				{
					if (typeof callback === 'function')
					{
						callback(null, 'Received non-zero status code');
						callback = undefined;
					}

					return;
				}

				callback(data.payload, null);
				callback = undefined;
			}
		});

		setTimeout(function ()
		{
			if (typeof callback === 'function')
			{
				callback(null, 'Timeout');
			}
		}, 10000);
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

		var route = Ajaxsite.router.find(url);

		if (route === undefined)
		{
			return false;
		}

		if (!data._prerendered)
		{
			Ajaxsite.content.html(Ajaxsite.renderLoading());
		}

		Ajaxsite.rsrc.loadBundles(route.rsrc_bundles, function ()
		{
			data = data || {};
			data._url = url;

			route.run_handler(data);
		});

		return true;
	};

	/**
	 * Handle errors
	 *
	 * @param string message
	 * @param bool isFatal
	 */
	Ajaxsite.error = function (message, isFatal)
	{
		if (isFatal)
		{
			// In case of a fatal error we want to clear the page where
			// the error occurred
			Ajaxsite.content.html('<h1>Error</h1><p>' + message + '</p>');
		}
		else
		{
			alert('Error: ' + message);
		}

		console.error((isFatal ? 'Fatal error' : 'Error') + ': ' + message);
	};

	/**
	 * Display a 404 page (call the 404 handler)
	 */
	Ajaxsite.call404 = function ()
	{
		Ajaxsite.handlers['404'](window.location.pathname);
	};

	/**
	 * Render a loading page
	 */
	Ajaxsite.renderLoading = function ()
	{
		return 'Loading...';
	};

	/**
	 * Resource manager
	 */
	Ajaxsite.rsrc = {
		/**
		 * Loaded files
		 */
		_loaded: {},

		/**
		 * Is initialized?
		 */
		_initialized: false,

		initialize: function ()
		{
			if (Ajaxsite.rsrc._initialized === true)
			{
				return;
			}

			Ajaxsite.rsrc._initialized = true;

			// TODO Add already loaded files to Ajaxsite.rsrc._loaded
		},

		/**
		 * Load multiple resource bundles at once
		 */
		loadBundles: function (bundleNames, callback)
		{
			if (!(bundleNames instanceof Array) ||
				bundleNames.length === 0)
			{
				callback();
				return;
			}

			var cb_count = bundleNames.length;
			var cb = function ()
			{
				if (--cb_count === 0 &&
					typeof callback === 'function')
				{
					callback();
				}
			};

			for (var i = 0, c = bundleNames.length; i < c; i++)
			{
				var status = Ajaxsite.rsrc.loadBundle(bundleNames[i], cb);

				if (status === false)
				{
					cb();
				}
			}
		},

		/**
		 * Load a resource bundle
		 */
		loadBundle: function (bundleName, callback)
		{
			if (bundleName instanceof Array)
			{
				Ajaxsite.rsrc.loadBundles(bundleName);
				return;
			}

			if (App.rsrc_bundles[bundleName] === undefined)
			{
				return false;
			}

			if (App.rsrc_prod === true)
			{
				Ajaxsite.rsrc.loadFile(bundleName, callback);
				return;
			}

			var bundle = App.rsrc_bundles[bundleName];
			var cb_count = bundle.files.length;
			var cb = function ()
			{
				if (--cb_count === 0 &&
					typeof callback === 'function')
				{
					callback();
				}
			};

			for (var i = 0, c = bundle.files.length; i < c; i++)
			{
				Ajaxsite.rsrc.loadFile(bundle.files[i], cb);
			}
		},

		loadFile: function (file, callback)
		{
			if (Ajaxsite.rsrc._loaded[file] === true)
			{
				if (typeof callback === 'function')
				{
					callback();
				}

				return;
			}

			var ext = file.split('.').pop();

			if (ext === 'css')
			{
				// TODO Load qith AJAX
				$('head').append($('<link>')
					.attr('rel', 'stylesheet')
					.attr('type', 'text/css')
					.attr('href', App.rsrc_root + '/' + file));

				Ajaxsite.rsrc._loaded[file] = true;

				if (typeof callback === 'function')
				{
					callback();
				}
			}
			else if (ext === 'js')
			{
				jQuery.ajax({
					url: App.rsrc_root + '/' + file,
					dataType: 'script',
					timeout: 7000,
					complete: function ()
					{
						if (typeof callback === 'function')
						{
							callback();
						}

						Ajaxsite.rsrc._loaded[file] = true;
					}
				});
			}
			else
			{
				callback();
			}
		}
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
		var a404_block = new Ajaxsite.Block();
		a404_block.html(Ajaxsite.renderLoading());

		Ajaxsite.data.template('404', function (template, error)
		{
			if (!error)
			{
				a404_block.html(template);
			}
		});

		a404_block.html('404 Not Found');

		Ajaxsite.content.html(a404_block.placeholder());
	};

	Ajaxsite.initialize();
})(Ajaxsite);

