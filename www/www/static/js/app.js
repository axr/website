window['App'] = window['App'] || {};

(new Rsrc.File('js/app.js')).provide(function ()
{
	var App = window['App'];

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
	 * App events
	 */
	App.Event = {
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
		 * @param {string} eventName
		 * @param {function()} callback
		 */
		on: function (eventName, callback)
		{
			this._events[eventName] = this._events[eventName] || [];
			this._events[eventName].push(callback);
		},

		/**
		 * Trigger a page event.
		 * The callback gets called after all event handlers have returned
		 *
		 * @param {string} eventName
		 * @param {function()} callback
		 */
		trigger: function (eventName, callback)
		{
			if (typeof callback !== 'function')
			{
				callback = function () {};
			}

			var handlers = this._events[eventName] || [];

			var mc_count = handlers.length;
			var mc = function ()
			{
				if (--mc_count === 0 && typeof callback === 'function')
				{
					callback();
				}
			};

			for (var i = 0, c = handlers.length; i < c; i++)
			{
				handlers[i]();
				mc();
			}
		}
	};

	/**
	 * Cache system
	 */
	App.Cache = {
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
				item.version !== App.vars.version ||
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

	App.LoadOnce = function ()
	{
		this._loading = false;
		this._callbacks = [];
		this._handler = null;

		this.callback = function (callback)
		{
			if (typeof this._handler === 'function')
			{
				this._handler(callback);
			}
			else
			{
				this._callbacks.push(callback);
			}
		};

		this.load = function (loader)
		{
			if (this._loading === false)
			{
				loader();
				this._loading = true;
			}
		};

		this.handle = function (handler)
		{
			this._handler = handler;

			for (var i = 0, c = this._callbacks.length; i < c; i++)
			{
				if (typeof this._callbacks[i] === 'function')
				{
					handler(this._callbacks[i]);
				}
			}
		};
	};

	/**
	 * Utilities
	 */
	App.Util = {
		/**
		 * Format dates into "x units ago" format. If the timestamp is not a number
		 * "some time ago" is returned.
		 *
		 * @param {integer} timestamp
		 * @return string
		 */
		format_date_ago: function (timestamp)
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
		},

		/**
		 * Scroll to the first element that has a `data-hash` attribute value
		 * matching to the argument `hash.
		 *
		 * @param {string} hash
		 */
		scroll_to_hash: function (hash)
		{
			var offset = $('[data-hash]').not(function ()
			{
				return $(this).attr('data-hash') !== hash;
			}).offset();

			if (offset !== null && !isNaN(offset.top))
			{
				$('html, body').animate({
					scrollTop: offset.top
				}, 800);
			}
		}
	};

	/**
	 * Template
	 *
	 * @param {string} name
	 */
	App.Template = function (name)
	{
		var that = this;
		var prototype = App.Template.prototype;

		{
			prototype._instances = prototype._instances || {};

			if (prototype._instances[name] !== undefined)
			{
				return prototype._instances[name];
			}

			prototype._instances[name] = this;
		}

		/**
		 * Loader
		 *
		 * @type {App.LoadOnce}
		 */
		this._loader = new App.LoadOnce();

		/**
		 * Name of the template
		 *
		 * @type {string}
		 */
		this.name = name;

		/**
		 * Request access to this template
		 *
		 * @param {function(string, App.Error)} callback
		 */
		this.request = function (callback)
		{
			this._loader.callback(callback);
			this._loader.load(function ()
			{
				if (App.Cache.get('App.Template?name=' + this.name))
				{
					that._loader.handle(function (callback)
					{
						callback(App.Cache.get('App.Template?name=' + this.name), null);
					});

					return;
				}

				$.ajax({
					url: App.vars.www_url + '/_ajax/template?callback=?',
					data: {
						name: that.name
					},
					dataType: 'jsonp',
					success: function (data)
					{
						if (typeof data !== 'object' || data.status !== 0)
						{
							that._loader.handle(function (callback)
							{
								callback(null, new App.Error('App.Template.RequestError'));
							});

							return;
						}

						App.Cache.set('App.Template?name=' + that.name, data.payload.template, {
							persistent: true
						});

						that._loader.handle(function (callback)
						{
							callback(data.payload.template, null);
						});
					},
					error: function ()
					{
						that._loader.handle(function (callback)
						{
							callback(null, new App.Error('App.Template.RequestError'));
						});
					}
				});
			});
		}
	};

	/**
	 * Modal box
	 */
	App.Modal = function (html, options)
	{
		var that = this;

		/**
		 * Contents of the modal box
		 *
		 * @type {string}
		 */
		this._html = html;

		/**
		 * Options
		 *
		 * @type {Object<string, *>}
		 */
		this._options = options || {};

		/**
		 * Initialize
		 */
		this.initialize = function ()
		{
			if ($('#as_modal').length === 0)
			{
				$('body').prepend(
					'<div id="as_modal">' +
						'<div id="as_modal_wrap">' +
							'<div class="inner">' +
								'<div class="content"></div>' +
							'</div>' +
						'</div>' +
					'</div>');
			}
		};

		/**
		 * Show a modal box
		 */
		this.show = function ()
		{
			this.initialize();
			this.hide();

			if (!(this._options.size instanceof Array) ||
					this._options.size.length !== 2)
			{
				this._options.size = [500, 375];
			}

			$('#as_modal > div > .inner')
				.css('width', this._options.size[0] + 'px')
				.css('height', this._options.size[1] + 'px')
				.css('right', '-' + (this._options.size[0] / 2) + 'px')
				.css('bottom', '-' + (this._options.size[1] / 2) + 'px');
			$('#as_modal > div > .inner > .content').html(this._html);

			$('#as_modal').on('click', '._as_modal_close', function ()
			{
				that.hide();
			});

			$('#as_modal').show();
		};

		/**
		 * Hide the modal box
		 */
		this.hide = function ()
		{
			$('#as_modal').hide();
			$('#as_modal > div > .inner > .content').empty();
		};
	};

	/**
	 * Google Analytics methods
	 */
	App.GoogleAnalytics = {
		/**
		 * Initialize GA
		 *
		 * @param {string} account
		 */
		initialize: function (account)
		{
			// Initialize GA queue
			window._gaq = window._gaq || [];
			window._gaq.push(['_setAccount', account]);
			//window._gaq.push(['_trackPageview']);

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
		},

		queue: function (data)
		{
			window._gaq.push(data);
		}
	};

	/**
	 * Methods for communicating with Twitter and stuff.
	 */
	App.Twitter = {
		_last_tweet_loader: new App.LoadOnce(),

		/**
		 * Makes your tweets beautiful
		 *
		 * @param {string} tweet
		 * @return string
		 */
		beautify_tweet: function (tweet)
		{
			var parseURL = function (tweet)
			{
				return tweet.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&\?\/.=]+/g,
					function (url)
				{
					return url.link(url);
				});
			};

			var parseUsername = function (tweet)
			{
				return tweet.replace(/[@]+[A-Za-z0-9-_]+/g, function (u)
				{
					return u.link('https://twitter.com/#!/' + u.replace('@', ''));
				});
			};

			var parseHashtag = function (tweet)
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
		},

		/**
		 * Get our last tweet
		 *
		 * @param {function(string, App.Error)}
		 */
		get_last_tweet: function (callback)
		{
			var that = this;

			this._last_tweet_loader.callback(callback);
			this._last_tweet_loader.load(function ()
			{
				if (App.Cache.get('App.Twitter.last_tweet'))
				{
					that._loader.handle(function (callback)
					{
						callback(App.Cache.get('App.Twitter.last_tweet'), null);
					});

					return;
				}

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
					dataType: 'jsonp',
					success: function (data)
					{
						if (data[0] === undefined)
						{
							that._last_tweet_loader.handle(function (callback)
							{
								callback(null, new App.Error('App.Twitter.APIError'));
							});

							return;
						}

						var timestamp = Date.parse(data[0].created_at) / 1000;
						var tweet = App.Twitter.beautify_tweet(data[0].text) +
							' &mdash; ' + App.Util.format_date_ago(timestamp);

						App.Cache.set('App.Twitter.last_tweet', tweet);

						that._last_tweet_loader.handle(function (callback)
						{
							callback(tweet, null);
						});
					},
					error: function ()
					{
						that._last_tweet_loader.handle(function (callback)
						{
							callback(null, new App.Error('App.Twitter.APIError'));
						});
					}
				});
			});
		}
	};

	/**
	 * Methods for communicating with GitHub
	 */
	App.GitHub = {
		_activity_loading: false,

		/**
		 * Get GitHub activity
		 *
		 * @param {function} callback (events, error)
		 */
		get_activity: function (callback)
		{
			var that = this;

			if (App.Cache.get('App.GitHub.activity'))
			{
				callback(App.Cache.get('App.GitHub.activity'), null)
				return;
			}

			if (this._activity_loading === true)
			{
				setTimeout(function ()
				{
					App.GitHub.get_activity(callback);
				}, 200);

				return;
			}

			this._activity_loading = true;

			$.ajax({
				url: App.vars.www_url + '/_ajax/ghactivity?callback=?',
				method: 'get',
				data: {
					count: 10
				},
				dataType: 'jsonp',
				success: function (data)
				{
					if (data.status !== 0)
					{
						callback(null, new App.Error('App.GitHub.APIError', {
							response_status: data.status,
							response_error: data.error
						}));

						return;
					}

					App.Cache.set('App.GitHub.activity', data.payload.events, {
						persistent: true,
						max_age: 1800
					});
					that._activity_loading = false;

					callback(data.payload.events, null);
				},
				error: function (xhr, text_status, error_thrown)
				{
					that._activity_loading = false;

					callback(null, new App.Error('App.GitHub.APIError', {
						text_status: text_status,
						error_thrown: error_thrown
					}));
				}
			});
		}
	};
});
