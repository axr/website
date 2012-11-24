window['App'] = window['App'] || {};

(function (App)
{
	App.data = App.data || {};
	App.util = App.util || {};

	/**
	 * Initialize the app.
	 */
	App.initialize = function ()
	{
		window.withApp = function (callback)
		{
			callback(App);
		};

		if (App._onInit !== undefined && App._onInit instanceof Array)
		{
			for (var i = 0, c = App._onInit.length; i < c; i++)
			{
				App._onInit[i](App);
			}
		}

		// Initialize GA queue
		window._gaq = window._gaq || [];
		window._gaq.push(['_setAccount', App.site.ga_account]);
		window._gaq.push(['_trackPageview']);

		// Load GA tracker code
		(function ()
		{
			var ga = document.createElement('script');
			ga.type = 'text/javascript';
			ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
		})();
	};

	/**
	 * Display an error to the user
	 * A fatal error is an error that occurrs during generation of the
	 * page and due to which the page cannot be displayed
	 *
	 * @param {string} error
	 * @param {boolean} fatal
	 */
	App.error = function (error, fatal)
	{
		fatal = fatal || false;
		App.modal.show('<strong>Error:</strong> ' + error);
	};

	/**
	 * Error
	 *
	 * @param {string} name
	 * @param {object} data
	 */
	App.Error = function (name, data)
	{
		this.name = name;

		/**
		 * Convert the error to string
		 *
		 * @return string
		 */
		this.to_s = function ()
		{
			return 'Error: ' + name + ': ' + JSON.stringify(data);
		};

		/**
		 * Show the error to the user
		 */
		this.show = function ()
		{
			alert(this.to_s());
		};

		for (var key in data)
		{
			if (key === 'show')
			{
				continue;
			}

			this[key] = data[key];
		}
	};

	/**
	 * Cache system
	 */
	App.cache = {
		_cache: {},

		/**
		 * Save data into cache.
		 *
		 * options:
		 * - {boolean} persistent
		 * - {number} max_age
		 *
		 * @param {string} key
		 * @param {*} data
		 * @param {Object} options
		 */
		set: function (key, data, options)
		{
			this._cache[key] = data;

			if ((options || {}).persistent === true)
			{
				var item = {
					version: App.version,
					expires: (new Date()).getTime() + (options.max_age || 30000000),
					data: data
				};

				if (options.max_age && !isNaN(options.max_age))
				{
					item.expires = (new Date()).getTime() + (options.max_age * 1000);
				}

				window.localStorage.setItem('cache:' + key, JSON.stringify(item));
			}
		},

		/**
		 * Get data from cache
		 *
		 * @param {string} key
		 * @return mixed
		 */
		get: function (key)
		{
			if (this._cache[key] !== undefined)
			{
				return this._cache[key];
			}

			var item = window.localStorage.getItem('cache:' + key);

			try
			{
				item = JSON.parse(item);
			}
			catch (e)
			{
				window.localStorage.removeItem('cache:' + key);
				return undefined;
			}

			if (item === null ||
				item.version !== App.version ||
				(!isNaN(item.expires) && (new Date()).getTime() > item.expires))
			{
				window.localStorage.removeItem('cache:' + key);
				return undefined;
			}

			return item.data;
		},

		/**
		 * Remove an item from the cache
		 *
		 * @param {string} key
		 */
		rm: function (key)
		{
			delete this._cache[key];
		}
	};

	/**
	 * A block class
	 *
	 * @constructor
	 */
	App.Block = function (id)
	{
		var that = this;

		/**
		 * Generate a random id
		 */
		this.id = (id !== undefined) ? id : ((Math.random()*0xFFFFFF).toString(32) + (Math.random()*0xFFFFFF).toString(32)).replace(/\./g, '');

		/**
		 * HTML for the block
		 *
		 * @private
		 */
		this._html = null;

		/**
		 * Fields
		 *
		 * @private
		 */
		this._fields = {};

		/**
		 * Replace the placeholder
		 */
		this.html = function (html)
		{
			this._html = html;
			$('.as_block_' + this.id).html(html);
		};

		/**
		 * Append to block's HTML
		 */
		this.append = function (html)
		{
			this.html((this._html || '') + html);
		};

		/**
		 * Set value for a field
		 *
		 * @param {string} name
		 * @param {string} value
		 */
		this.setField = function (name, value)
		{
			this._fields[name] = value;
			$('.as_field_' + this.id + '_' + name).html(value);
		};

		/**
		 * Return a placeholder
		 */
		this.placeholder = function ($el)
		{
				var $el = $('<div>')
				.attr('class', 'as_block_' + this.id)
				.html(this._html);

			return $('<div>').append($el).html();
		};

		/**
		 * Return placeholder for a field
		 *
		 * @param {string} name
		 * @param {string} value default value
		 * @return string
		 */
		this.field = function (name, value)
		{
			var $el = $('<span>')
				.attr('class', 'as_field_' + this.id + '_' + name)
				.html(this._fields[name] || value || '');

			return $('<div>').append($el).html();
		};

		/**
		 * Count how many placeholders there are currently in the DOM
		 *
		 * @return int
		 */
		this.count = function ()
		{
			return $('.as_block_' + this.id).length;
		};

		// Wait for element to be created
		var interval = setInterval(function ()
		{
			if ($('.as_block_' + that.id).length > 0)
			{
				clearInterval(interval);
				$('.as_block_' + that.id).html(that._html);
			}
		}, 100);
	};

	/**
	 * Calls `finalCallback` after the returned function has been called
	 * `count` times.
	 *
	 * @param {integer} count
	 * @param {function()} finalCallback
	 * @return function
	 */
	App.util.multiCallback = function (count, finalCallback)
	{
		return function ()
		{
			if (--count === 0 && typeof finalCallback === 'function')
			{
				finalCallback();
			}
		};
	};

	/**
	 * Makes your tweets beautiful
	 *
	 * @param {string} tweet
	 * @return string
	 */
	App.util.beautifyTweet = function (tweet)
	{
		parseURL = function (tweet)
		{
			return tweet.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&\?\/.=]+/g,
				function (url)
			{
				return url.link(url);
			});
		};

		parseUsername = function (tweet)
		{
			return tweet.replace(/[@]+[A-Za-z0-9-_]+/g, function (u)
			{
				return u.link('https://twitter.com/#!/' + u.replace('@', ''));
			});
		};

		parseHashtag = function (tweet)
		{
			return tweet.replace(/[#]+[A-Za-z0-9-_]+/g, function (t)
			{
				return t.link('http://search.twitter.com/search?q=' +
					t.replace('#', '%23'));
			});
		};

		tweet = parseURL(tweet);
		tweet = parseUsername(tweet);
		tweet = parseHashtag(tweet);

		return tweet;
	};

	/**
	 * Format dates into "x units ago" format. If the timestamp is not a number
	 * "some time ago" is returned.
	 *
	 * @param {integer} timestamp
	 * @return string
	 */
	App.util.formatDateAgo = function (timestamp)
	{
		if (isNaN(timestamp))
		{
			return 'some time ago';
		}

		var diff = Math.floor((new Date()).getTime() / 1000) - timestamp;

		if (diff == 0) {
			return 'just now';
		}

		var unit = 'year', divide = 31556926;
		if (diff < 31556926) { unit = 'month', divide = 2628000; }
		if (diff < 2629744) { unit = 'week', divide = 604800; }
		if (diff < 604800) { unit = 'day', divide = 86400; }
		if (diff < 86400) { unit = 'hour', divide = 3600; }
		if (diff < 3600) { unit = 'minute', divide = 60; }
		if (diff < 60) { unit = 'second', divide = 1; }

		var value = Math.floor(diff / divide);

		return value + ' ' + unit + (value > 1 ? 's' : '') + ' ago';
	};

	App.pageEvent = {
		/**
		 * All registered events are stored in here
		 *
		 * @private
		 */
		_events: {},

		/**
		 * Register a event listever.
		 * The `page` should be in a path format (starting with /) but it
		 * does not have to reflect the actual page URL. The first segment
		 * is usually controller name.
		 *
		 * @param {string} eventType
		 * @param {string} page
		 * @param {function()} callback
		 */
		on: function (eventType, page, callback)
		{
			this._events[eventType] = this._events[eventType] || {};
			this._events[eventType][page] = this._events[eventType][page] || [];
			this._events[eventType][page].push(callback);
		},

		/**
		 * Trigger a page event.
		 * The callback gets called after all event handlers have returned
		 *
		 * @param {string} eventType
		 * @param {string} page
		 * @param {function()} callback
		 */
		trigger: function (eventType, page, callback)
		{
			if (typeof callback !== 'function')
			{
				callback = function () {};
			}

			if (page !== '*')
			{
				callback = function ()
				{
					App.pageEvent.trigger(eventType, '*', callback);
				};
			}

			var handlers = (this._events[eventType] || {})[page] || [];
			var f = App.util.multiCallback(handlers.length, callback);

			for (var i = 0, c = handlers.length; i < c; i++)
			{
				handlers[i]();
				f();
			}
		}
	};

	/**
	 * Load a template
	 *
	 * @param {string} name
	 * @param {function(?string, ?string)} callback
	 */
	App.data.template = function (name, callback)
	{
		if (typeof callback !== 'function')
		{
			callback = function () {};
		}

		// We already have the template in cache
		if (App.cache.get('/template/' + name) !== undefined)
		{
			callback(App.cache.get('/template/' + name), null);
			return;
		}

		// The template is currently being loaded
		if (App.cache.get('/template/:loading/' + name) === true)
		{
			setTimeout(function ()
			{
				App.data.template(name, callback);
			}, 100);

			return;
		}

		App.cache.set('/template/:loading/' + name, true);

		$.ajax({
			url: App['/shared/www_url'] + '/_ajax/template?callback=?',
			data: {
				name: name
			},
			dataType: 'jsonp'
		}).success(function (data)
		{
			if (typeof data !== 'object' || data.status !== 0)
			{
				callback(null, 'Error loading template `' + name + '`. Returned status: ' + (data || {}).status);
				return;
			}

			App.cache.set('/template/' + name, data.payload.template, {
				persistent: true
			});
			App.cache.set('/template/:loading/' + name, false);

			callback(data.payload.template, false);
		}).error(function ()
		{
			App.cache.get('/template/:loading/' + name, false);
			callback(null, 'Error while loading template `' + name + '`');
		});
	};

	/**
	 * Get last tweet.
	 *
	 * @param {function(?string, ?string)}
	 */
	App.data.lastTweetForBox = function (callback)
	{
		if (App.cache.get('/lastTweetForBox'))
		{
			callback(App.cache.get('/lastTweetForBox'), null)
			return;
		}

		if (App.cache.get('/lastTweetForBox/:loading') === true)
		{
			setTimeout(function ()
			{
				App.data.lastTweetForBox(callback);
			}, 200);

			return;
		}

		App.cache.set('/lastTweetForBox/:loading', true);

		$.ajax({
			url: 'https://api.twitter.com/1/statuses/user_timeline.json?callback=?',
			method: 'get',
			data: {
				screen_name: 'axrproject',
				include_rts: 'true',
				include_entities: 'false',
				exclude_replies: 'true',
				trim_user: 'true',
				count: 10
			},
			dataType: 'jsonp'
		}).success(function (data)
		{
			if (data[0] === undefined)
			{
				callback(null, 'Error loading last tweet');
				return;
			}

			var timestamp = Date.parse(data[0].created_at) / 1000;
			var tweet = App.util.beautifyTweet(data[0].text) +
				' &mdash; ' + App.util.formatDateAgo(timestamp);

			App.cache.set('/lastTweetForBox', tweet);
			App.cache.set('/lastTweetForBox/:loading', false);

			if (typeof callback === 'function')
			{
				callback(tweet, null);
			}
		}).error(function ()
		{
			App.cache.set('/lastTweetForBox/:loading', false);
			callback(null, 'Error loading last tweet');
		});
	};

	/**
	 * Get GitHub activity
	 *
	 * @param object options
	 * @param function callback (events, error)
	 */
	App.data.githubActivity = function (options, callback)
	{
		if (typeof callback !== 'function')
		{
			callback = function () {};
		}

		options.count = options.count || 20;
		var cache_key = '/githubActivity/:count/' + options.count;

		if (App.cache.get(cache_key))
		{
			callback(App.cache.get(cache_key), null)
			return;
		}

		if (App.cache.get(cache_key + '/:loading') === true)
		{
			setTimeout(function ()
			{
				App.data.githubActivity(ptions, callback);
			}, 200);

			return;
		}

		App.cache.set(cache_key + '/:loading', true);

		$.ajax({
			url: App['/shared/www_url'] + '/_ajax/ghactivity?callback=?',
			method: 'get',
			data: {
				count: options.count
			},
			dataType: 'jsonp'
		}).success(function (data)
		{
			if (data.status !== 0)
			{
				callback(null, new App.Error('ResponseError', {
					response_status: data.status,
					response_error: data.error
				}));

				return;
			}

			App.cache.set(cache_key, data.payload.events, {
				persistent: true,
				max_age: 1800
			});
			App.cache.set(cache_key + '/:loading', false);

			callback(data.payload.events, null);
		}).error(function (jqXHR, text_status, error_thrown)
		{
			App.cache.set(cache_key + '/:loading', false);

			callback(null, new App.Error('RequestError', {
				text_status: text_status,
				error_thrown: error_thrown
			}));
		});
	};

	// Initialize the app
	$('document').ready(App.initialize);
})(window['App']);
