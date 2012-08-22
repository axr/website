window.App = window.App || {};

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

		for (var i = 0, c = App._onInit.length; i < c; i++)
		{
			App._onInit[i](App);
		}

		// Initialize GA queue
		window._gaq = window._gaq || [];
		window._gaq.push(['_setAccount', window.App.ga_account]);
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
	 * @param string error
	 * @param bool fatal
	 */
	App.error = function (error, fatal)
	{
		fatal = fatal || false;
		App.modal.show('<strong>Error:</strong> ' + error);
	};

	/**
	 * Cache system
	 */
	App.cache = {
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
	 * A block class
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
		 * Append to block's HTML
		 */
		this.append = function (html)
		{
			this.html((this._html || '') + html);
		};

		/**
		 * Set value for a field
		 *
		 * @param string name
		 * @param string value
		 */
		this.setField = function (name, value)
		{
			this._fields[name] = value;
			jQuery('.as_field_' + this.id + '_' + name).html(value);
		};

		/**
		 * Return a placeholder
		 */
		this.placeholder = function ($el)
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
		}, 100);
	};

	/**
	 * Calls `finalCallback` after the returned function has been called
	 * `count` times.
	 *
	 * @param int count
	 * @param function finalCallback
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
	 * @param string tweet
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
	 * Format dates into "x units ago" format.
	 *
	 * @param int timestamp
	 * @return string
	 */
	App.util.formatDateAgo = function (timestamp)
	{
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
		_events: {},

		/**
		 * Register a event listever.
		 * The `page` should be in a path format (starting with /) but it
		 * does not have to reflect the actual page URL. The first segment
		 * is usually controller name.
		 *
		 * @param string eventType
		 * @param string page
		 * @param function callback
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
		 * @param string eventType
		 * @param string page
		 * @param function callback
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
	 * @param string name
	 * @param function callback(template, error)
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

		App.cache.get('/template/:loading/' + name, true);

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
				callback(null, 'Error loading template `' + name + '`. Returned status: ' + (data || {}).status);
				return;
			}

			App.cache.get('/template/' + name, data.payload.template);
			App.cache.get('/template/:loading/' + name, false);

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
	 * @param callback(tweet, error)
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

	// Initialize the app
	$('document').ready(App.initialize);
})(App);

