window['App'] = window['App'] || {};

(function (App)
{
	App.pageEvent.on('load_init', '/downloads', function ()
	{
		$('#downloads .rtable > .release > header a.version').on('click', function (e)
		{
			e.preventDefault();

			var selected = $(this).parents('.release').find('.groups').toggle();

			$(this).parents('.rtable').find('.groups').each(function (i, e)
			{
				if (e !== selected[0])
				{
					$(e).hide();
				}
			});
		});
	});

	App.pageEvent.on('load', '/downloads', function ()
	{
		$('#downloads .rtable .groups').hide();
		$('#downloads .rtable[data-key=browser] .groups').show();
	});
})(window['App']);

window['App'].Rsrc.file('js/downloads.js').set_loaded();
