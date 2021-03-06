(function ()
{
	Core.Router.instance().once(/^\/downloads\/?$/, function ()
	{
		$('body').append('<div id="downloads_checksums"></div>');
		$('body').append('<div id="downloads_checksums_overlay"></div>');

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

		$('#downloads .rtable .pkggroup tbody td.dl a.dl').on('click', function (e)
		{
			window._gaq.push(['_trackEvent', 'Downloads', 'DownloadLink', $(this).attr('data-filename')]);
		});

		$('#downloads_checksums_overlay').on('click', function (e)
		{
			$('#downloads_checksums').empty().hide();
			$('#downloads_checksums_overlay').hide();
		});

		$('#downloads .rtable .pkggroup tbody td.dl a.checksums').on('click', function (e)
		{
			e.preventDefault();

			var popup = $('#downloads_checksums');
			var checksums = $(this).attr('data-checksums');

			try
			{
				checksums = JSON.parse(checksums);
			}
			catch (e)
			{
				return;
			}

			popup
				.css('left', '-9999px')
				.css('top', '-9999px')
				.empty()
				.show();

			var insert_pair = function (name, hash)
			{
				popup.append('<dl><dt>{{name}}</dt><dd>{{hash}}</dd></dl>'
					.replace('{{name}}', name.toUpperCase())
					.replace('{{hash}}', checksums[name]));
			};

			// Insert the checksums in a specific order
			checksums['md5'] && insert_pair('md5', checksums['md5']);
			checksums['sha1'] && insert_pair('sha1', checksums['sha1']);

			var offset_link = $(this).offset();

			var x = offset_link.left + ($(this).width() / 2) - (popup.width() / 2);
			var y = offset_link.top - popup.height() - 35;

			popup
				.css('left', x + 'px')
				.css('top', y + 'px')
				.show();

			$('#downloads_checksums_overlay').show();
		});
	});

	Core.Router.instance().on(/^\/downloads\/?$/, function ()
	{
		$('#downloads .rtable .release').addClass('collapsed');
		$('#downloads .rtable[data-key=browser] .release:first-child').removeClass('collapsed');
	});
})();
