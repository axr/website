(function ($)
{
	var beautifyTweet = function (tweet)
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
	var formatDateAgo = function (timestamp)
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

	/**
	 * Load a tweet for the latest tweet box
	 */
	var loadTweet = function (page)
	{
		var page = parseInt(page || 1);
		page = isNaN(page) ? 1 : page;

		$.ajax({
			url: 'https://api.twitter.com/1/statuses/user_timeline.json?callback=?',
			method: 'get',
			data: {
				screen_name: 'axrproject',
				include_rts: 'true',
				include_entities: 'false',
				exclude_replies: 'true',
				trim_user: 'true',
				count: 10,
				page: page
			},
			dataType: 'jsonp'
		}).success(function (data)
		{
			if (typeof data[0] !== 'object')
			{
				if (page < 4)
				{
					loadTweet(page + 1);
				}
				else
				{
					$('.last_tweet > .tweet_container').html('Error loading the tweet');
				}

				return;
			}

			var tweet = data[0];
			var fuckIE = tweet.created_at.split(' ');
			var date = Date.parse(fuckIE[1] + ' ' + fuckIE[2] + ', ' +
				fuckIE[5] + ' ' + fuckIE[3] + ' UTC');
			var timestamp = (new Date(date)).getTime();
			var time = formatDateAgo(Math.floor(timestamp / 1000));

			$('.last_tweet > .tweet_container')
				.html(beautifyTweet(tweet.text) + ' &mdash; ' + time);
		}).error(function ()
		{
			$('.last_tweet > .tweet_container').html('Error loading the tweet');
		});
	};

	/**
	 * Load GitHub activity
	 */
	var loadGHActivity = function ()
	{
		/**
		 * Load GitHub activity for the activity box
		 */
		$.getJSON('/activity.php', function (data)
		{
			$('#ghactivity').empty();

			for (var i = 0; i < 5; i++)
			{
				if (data.activity[i] === undefined)
				{
					break;
				}

				var item = data.activity[i];

				$('#ghactivity')
					.append('<li><div class="inner">' + item.title + '</div></li>');
			}
		});
	};

	var insertCodeFrame = function (block)
	{
		var language = $(block).attr('data-language');
		var code = $(block).html().split('\n');
		var lines = [];

		for (var i = 0, c = code.length; i < c; i++)
		{
			lines.push({
				number: i,
				line: code[i].replace('\t', '    ') + '\n'
			});
		}

		Ajaxsite.template('code_frame', function (template)
		{
			$(block).replaceWith(Mustache.render(template, {
				language: language,
				lines: lines
			}));
		});
	}

	/**
	 * Dropdown menu
	 * @fixme Shouldn't this be implemented in CSS?
	 */
	$("#container > header > nav > ul > li").hover(function ()
	{
		$(this).closest('li').addClass('hover').find(".sections")
			.removeClass('hidden');
	}, function ()
	{
		$(this).closest('li').removeClass('hover').find(".sections")
			.addClass('hidden');
	});

	/**
	* Dropdown for user menu
	*/
	$("#container > header > .secondary > div.user_menu").hover(function ()
	{
		$(this).addClass('hover').find(".dropdown").removeClass('hidden');
	}, function ()
	{
		$(this).removeClass('hover').find(".dropdown").addClass('hidden');
	});

	/**
	 * Show popup for the flaoting social buttons
	 */
	$("#container > .share > ul > li > a").click(function (event)
	{
		event.preventDefault();

		var page = $(this).attr("href");
		var popUpHeight = 245;
		var popUpWidth = 730;
		var top = ($(window).height() - popUpHeight) / 2;
		var left = ($(window).width() - popUpWidth) / 2;
		var options = 'toolbar=no, location=no, directories=no, status=no, ' +
			'menubar=no, scrollbars=no, resizable=yes, width=' + popUpWidth +
			', height=' + popUpHeight + ', top=' + top + ', left=' + left;
		window.open(page, "", options);
	});

	/**
	 * Back to top link animation
	 */
	$("#container > footer > a").click(function (event)
	{
		event.preventDefault();

		$('html, body').animate({
			scrollTop: 0
		}, 800);
	});

	/**
	 * Event handlers for madal boxes
	 */
	{
		$('.modal').hide().removeClass('hidden');

		$('.modal').bind('show',function ()
		{
			$(this).fadeIn('fast');
		});

		$('.modal').bind('hide',function ()
		{
			$(this).fadeOut('fast');
		});

		$('.modal .modal_close').click(function ()
		{
			$(this).closest('.modal').trigger('hide');

			return false;
		});

		$('.modal').click(function (e)
		{
			if (e.target != this)
			{
				return;
			}

			$(this).trigger('hide');
		});
	}

	/**
	 * Show "Join the revolution" modal box
	 */
	$('#action_button').click(function ()
	{
		$('#joining').trigger('show');
		return false;
	});

	/**
	 * Handle search form
	 */
	$('header > .secondary > form').on('submit', function (e)
	{
		e.preventDefault();

		var keys = $(this).find('input[type=search]').val();
		var type = /^\/wiki\//.test(window.location.pathname) ? 'wiki' : 'mixed';
		var url = '/search/' + type + '/' + encodeURIComponent(keys);

		if (typeof Ajaxsite === 'undefined')
		{
			window.location = url;
		}
		else
		{
			Ajaxsite.url(url);
		}
	});

	$(document).ready(function ()
	{
		loadTweet();

		Rainbow.onHighlight(function (block)
		{
			insertCodeFrame(block);
		});

		if (/^\/get-involved(\/|$)/.test(window.location.pathname))
		{
			loadGHActivity();
		}
	});
})(jQuery);

