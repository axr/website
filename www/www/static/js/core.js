window['Core'] = {};

(function (Core)
{
	if (!window.console)
	{
		window.console = {
			log: function () {},
			warn: function () {}
		};
	}

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
	Core.site = {};

	Core.site.on_ready = function ()
	{
		Core.social.LastTweet.instance().get(function (tweet, error)
		{
			$('#container > footer ._last_tweet')
				.html(tweet || error.message);
		});

		Core.CodeBox.find_all(document.body);
		Core.Router.instance().trigger_url_changed(window.location.pathname);
	};

	Core.site.scroll_to_hash = function ()
	{
		var hash = decodeURIComponent(window.location.hash.replace(/^#/, ''));
		var offset = $('[data-hash]').not(function ()
		{
			return $(this).attr('data-hash') !== hash;
		}).offset();

		if (offset !== null && !isNaN((offset || {}).top))
		{
			$('html, body').animate({
				scrollTop: offset.top
			}, 800);
		}
	};

	Core.site.load_ga = function ()
	{
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
	 * A simple router system
	 */
	Core.Router = function ()
	{
		var that = this;

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
		 * This should be called when the current URL changes
		 *
		 * @param {string} url
		 */
		this.trigger_url_changed = function (url)
		{
			if (url === this._url)
			{
				return;
			}

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
	Core.util.format_date_ago = function (timestamp, limit)
	{
		if (isNaN(timestamp))
		{
			return 'some time ago';
		}

		var diff = Math.floor((new Date()).getTime() / 1000) - timestamp;

		if (diff == 0) {
			return 'just now';
		}

		if (!isNaN(limit) && diff > limit)
		{
			return 'some time ago';
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
				return t.link('https://twitter.com/search?src=typd&q=' +
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

		/**
		 * The code that this code box contains
		 *
		 * @type {string}
		 */
		this.code = this.element.find('code').html();

		/**
		 * Syntax of the code
		 *
		 * @type {string}
		 */
		this.syntax = null;

		var match = this.code.match(/^(\/\/|#|\/\*|([<]|&lt;)!--) language=([a-z]+)( \*\/| --([>]|&gt;))?\n/);
		if (match instanceof Array)
		{
			this.syntax = match[3];
			this.code = this.code.replace(/^.+?\n/, '');
		}
		else if ($(element).find('code').is('[data-language]'))
		{
			this.syntax = $(element).find('code').attr('data-language');
		}

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
		if (typeof Prism.languages[this.syntax] === 'object')
		{
			var lines = Prism.highlight(this.code, Prism.languages[this.syntax])
				.split('\n');
		}
		else
		{
			var lines = this.code.split('\n');
		}

		var html = [];
		var html_linenos = [];
		var html_code = [];

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

			html_linenos.push('<div>' + (i + 1) + '</div>');
			html_code.push(line);
		}

		html.push('<div class="uiCodeBox">');
		html.push('<div class="linenos">' + html_linenos.join('') + '</div>');
		html.push('<pre>' + html_code.join('\n') + '</pre>');
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
				url: 'http://axrproject.org/bin/twitter_you_suck.php?callback=?',
				method: 'get',
				dataType: 'jsonp',
				success: function (data)
				{
					if (data[0] === undefined)
					{
						that._error = new Error('Error loading the latest tweet');
						return;
					}

					var timestamp = Date.parse(data[0].created_at) / 1000;
					var limit = 86400 * 45;

					that._tweet = Core.util.beautify_tweet(data[0].text) +
						' &mdash; ' + Core.util.format_date_ago(timestamp, limit);
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
				url: '/_ajax/ghactivity?callback=?',
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
