window['Core'] = {};

(function (Core)
{
	var instance_getter = function (klass)
	{
		return function ()
		{
			if (!(klass._instance instanceof klass))
			{
				klass._instance = new klass();
			}

			return klass._instance;
		};
	};

	Core.util = {};
	Core.social = {};

	/**
	 * A simple router system
	 */
	Core.Router = function ()
	{
		this._routes = [];
		this._url = null;

		/**
		 * Call the callbacks
		 */
		this._route = function ()
		{
			if (this._url === null)
			{
				return;
			}

			for (var i = 0; i < this._routes.length; i++)
			{
				var route = this._routes[i];

				if (route.regex.test(this._url))
				{
					route.callback.call(null);

					if (route.once === true)
					{
						this._routes.splice(i--, 1);
					}
				}
			}
		};

		/**
		 * Update the current URL. This will not actually navigate anywhere,
		 * it'll just call the callbacks.
		 *
		 * @param {string} url
		 */
		this.update = function (url)
		{
			this._url = url;
			this._route();
		};

		/**
		 * Call the callback, when the current URL matches the regex.
		 *
		 * @param {RegExp} regex
		 * @param {function} callback
		 */
		this.on = function (regex, callback, options)
		{
			this._routes.push({
				regex: regex,
				callback: callback,
				once: !!(options || {}).once
			});
		};

		/**
		 * Like `on`, but the callback will be called only once
		 */
		this.once = function (regex, callback)
		{
			this.on(regex, callback, { once: true });
		};
	};

	/**
	 * @return {Core.Router}
	 */
	Core.Router.instance = instance_getter(Core.Router);

	/**
	 * Format dates into "x units ago" format. If the timestamp is not a number
	 * "some time ago" is returned.
	 *
	 * @param {integer} timestamp
	 * @return string
	 */
	Core.util.format_date_ago = function (timestamp)
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

	/**
	 * Makes the tweets beautiful
	 *
	 * @param {string} tweet
	 * @return string
	 */
	Core.util.beautify_tweet = function (tweet)
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
	};

	Core.CodeBox = function (element)
	{
		/**
		 * The element.
		 *
		 * @todo verify that the element is a <pre>
		 */
		this.element = element;

		this.render();
	};

	/**
	 * Replace the code block with something
	 */
	Core.CodeBox.prototype.replace_with = function (replacement)
	{
		this.element.replaceWith(replacement);
	};

	/**
	 * Render a code block
	 */
	Core.CodeBox.prototype.render = function ()
	{
		var code_element = this.element.find('code');
		var lines = code_element.html().split('\n');

		var html = [];
		html.push('<div class="uiCodeBox" data-language="raw">');

		for (var i = 0, c = lines.length; i < c; i++)
		{
			if (i + 1 === c && lines[i].length === 0)
			{
				// Don't insert the last blank line
				break;
			}

			// Convert tabs to spaces
			var line = lines[i].replace(/^[\t]+/, function (match)
			{
				return (new Array(match.length * 4 + 1)).join(' ')
			});

			html.push('<div class="line">');
			html.push('<div class="number">' + (i + 1) + '</div>');
			html.push('<code>' + line + '</code>');
			html.push('</div>');
		}

		html.push('</div>');

		this.replace_with(html.join(''));
	};

	/**
	 * Find all code blocks in the provided element and render them
	 *
	 * @param {object} element
	 */
	Core.CodeBox.find_all = function (element)
	{
		$(element).find('pre > code').each(function (i, element)
		{
			new Core.CodeBox($(element).parent());
		});
	};

	Core.Modal = function (html, options)
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
	 * Last tweet loader
	 */
	Core.social.LastTweet = function ()
	{
		var that = this;

		this._tweet = null;
		this._error = null;

		/**
		 * Load the last tweet
		 */
		this._load = function ()
		{
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
						that._error = new Error('Error loading the latest tweet');
						return;
					}

					var timestamp = Date.parse(data[0].created_at) / 1000;

					that._tweet = Core.util.beautify_tweet(data[0].text) +
						' &mdash; ' + Core.util.format_date_ago(timestamp);
				},
				error: function ()
				{
					that._error = new Error('Error loading the latest tweet');
				}
			});
		};

		/**
		 * Get the last tweet
		 *
		 * @param {function} callback
		 */
		this.get = function (callback)
		{
			if (this._tweet !== null ||
				this._error !== null)
			{
				callback(this._error ? null : this._tweet, this._error);
			}
			else
			{
				setTimeout(function ()
				{
					that.get(callback);
				}, 100);
			}
		};

		this._load();
	};

	/**
	 * @return {Core.social.LastTweet}
	 */
	Core.social.LastTweet.instance = instance_getter(Core.social.LastTweet);

	/**
	 * Latest GitHub activity loader
	 */
	Core.social.GitHubActivity = function ()
	{
		var that = this;

		this._activity = null;
		this._error = null;

		/**
		 * Load the latest activity
		 */
		this._load = function ()
		{
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
						that._error = 'Error loading:' + data.error;
						return;
					}

					that._activity = data.payload.events;
				},
				error: function (xhr, text_status, error_thrown)
				{
					that._error = 'Error loading: ' + error_thrown;
				}
			});
		};

		/**
		 * Get the latest activity
		 *
		 * @param {function} callback
		 */
		this.get = function (callback)
		{
			if (this._activity !== null ||
				this._error !== null)
			{
				callback(this._error ? null : this._activity, this._error);
			}
			else
			{
				setTimeout(function ()
				{
					that.get(callback);
				}, 100);
			}
		};

		this._load();
	};

	/**
	 * @return {Core.social.GitHubActivity}
	 */
	Core.social.GitHubActivity.instance = instance_getter(Core.social.GitHubActivity);
})(window['Core']);
