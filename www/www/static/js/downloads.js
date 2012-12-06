window['App'] = window['App'] || {};

(function (App)
{
	var package_info = {
		'axr-browser': 'The AXR browser for viewing HSS files',
		'axr': 'The AXR core library',
		'libaxr': 'The AXR core library',
		'axr-runtime': 'The AXR core library',
		'axr-devel': 'Tools for developing using the AXR core library',
		'libaxr-dev': 'Tools for developing using the AXR core library',
		'axr-doc': 'Documentation for the AXR core library',
		'libaxr-doc': 'Documentation for the AXR core library'
	};

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

		$('#downloads .rtable .pkggroup tbody td.size span').tipsy({
			gravity: 'w'
		});

		$('#downloads .rtable .pkggroup tbody td.name span').each(function (i, e)
		{
			if (typeof package_info[$(this).html()] === 'string')
			{
				$(e).tipsy({
					gravity: 'w',
					title: function ()
					{
						return package_info[$(this).html()];
					}
				});
			}
		});
	});

	App.pageEvent.on('load', '/downloads', function ()
	{
		$('#downloads .rtable .groups').hide();
		$('#downloads .rtable[data-key=browser] .groups').show();
	});
})(window['App']);

window['App'].Rsrc.file('js/downloads.js').set_loaded();
