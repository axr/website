window['App'] = window['App'] || {};

(new Rsrc.File('js/www/site.js')).provide(function ()
{
	$(document).ready(function ()
	{
		App.GoogleAnalytics.initialize(App.vars.ga_accounts['default']);
		App.GoogleAnalytics.queue(['_trackPageview']);

		(new Rsrc.File('js/code_box.js')).request(function (error)
		{
			App.CodeBox.find_all(document.body);
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
