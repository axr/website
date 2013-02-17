(new Rsrc.File('js/www/downloads.js')).provide(function ()
{
	App.WWW = App.WWW || {};

	App.WWW.Downloads = {
		_initialized: false,

		initialize: function ()
		{
			if (this._initialized === true)
			{
				return;
			}

			this._initialized = true;

			$('#downloads .rtable > .release > header a.version').on('click', function (e)
			{
				e.preventDefault();

				var selected = $(this).parents('.release').toggleClass('collapsed');

				$(this).parents('.release').each(function (i, e)
				{
					if (e !== selected[0])
					{
						$(e).removeClass('collapsed');
					}
				});
			});

			$('#downloads .rtable .pkggroup tbody td.dl a').on('click', function (e)
			{
				window._gaq.push(['_trackEvent', 'Downloads', 'DownloadLink', $(this).attr('data-filename')]);
			});
		}
	};

	App.Event.on('App.WWW.Downloads.load', function ()
	{
		App.WWW.Downloads.initialize();

		$('#downloads .rtable .release').addClass('collapsed');
		$('#downloads .rtable[data-key=browser] .release').removeClass('collapsed');
	});
});
