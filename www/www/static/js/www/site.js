window['App'] = window['App'] || {};

(new Rsrc.File('js/www/site.js')).provide(function ()
{
	$(document).ready(function ()
	{
		App.GoogleAnalytics.initialize(App.vars.ga_accounts['default']);
		App.GoogleAnalytics.queue(['_trackPageview']);
	});

	(new Rsrc.File('js/rainbow/rainbow.js')).request(function ()
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

			// If the first line is empty, remove it
			if (lines[0].line.replace(/\s+/, '').length === 0)
			{
				lines.splice(0, 1);
			}

			// If the last line is empty, remove it
			if (lines[lines.length - 1].line.replace(/\s+/, '').length === 0)
			{
				lines.splice(lines.length - 1, 1);
			}

			(new App.Template('code_frame')).request(function (template, error)
			{
				if (error)
				{
					return;
				}

				var html = Mustache.render(template, {
					language: language,
					lines: lines
				});

				if ($(block).parent().prop('tagName') === 'PRE')
				{
					$(block).parent().replaceWith(html);
				}
				else
				{
					$(block).replaceWith(html);
				}
			});
		});

		$(document).ready(function ()
		{
			Rainbow.color();
		});
	});

	/**
	 * Dropdown menu
	 * @fixme Shouldn't this be implemented in CSS?
	 */
	$('#container > header > nav > ul > li').on('hover', function ()
	{
		$(this).closest('li')
			.addClass('hover')
			.find('.sections')
			.removeClass('hidden');
	}, function ()
	{
		$(this).closest('li')
			.removeClass('hover')
			.find('.sections')
			.addClass('hidden');
	});

	/**
	 * Back to top link animation
	 */
	$('#container > footer .back_to_top').on('click', function (event)
	{
		event.preventDefault();

		$('html, body').animate({
			scrollTop: 0
		}, 800);
	});
});
