window.App = window.App || {};

(function (App)
{
	App.rsrc.loadBundle('js/bundle_rainbow.js', function ()
	{
		Rainbow.onHighlight(function (block)
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

			App.data.template('code_frame', function (template)
			{
				$(block).replaceWith(Mustache.render(template, {
					language: language,
					lines: lines
				}));
			});
		});
	});

	/**
	 * Dropdown menu
	 * @fixme Shouldn't this be implemented in CSS?
	 */
	$('#container > header > nav > ul > li').hover(function ()
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
	$('#container > header div.user_menu').hover(function ()
	{
		$(this).addClass('hover').find('.dropdown').removeClass('hidden');
	}, function ()
	{
		$(this).removeClass('hover').find('.dropdown').addClass('hidden');
	});

	/**
	 * Back to top link animation
	 */
	$('#container > footer > a').click(function (event)
	{
		event.preventDefault();

		$('html, body').animate({
			scrollTop: 0
		}, 800);
	});

	App.data.lastTweetForBox(function (tweet, error)
	{
		$('#container > footer .last_tweet .tweet_container')
			.html(tweet || error);
	});
})(window.App);

