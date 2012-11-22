(function ($)
{
	var oses = {
		win: 'Windows',
		osx: 'OS X',
		linux: 'Linux'
	};

	var arches = {
		'x86-64': 'x86-64 (64-bit)',
		'x86': 'x86 (32-bit)'
	};

	$('#downloads__ask_arch').appendTo('body');

	$('#downloads__ask_arch a.close').on('click', function (e)
	{
		e.preventDefault();
		$('#downloads__ask_arch').hide();
	});

	$('#downloads ul.dtable a.dlink').on('click', function (e)
	{
		e.preventDefault();

		var version = $(this).attr('data-version');
		var os = $(this).attr('data-os');

		if (window.axr_downloads === undefined ||
			window.axr_downloads[version] === undefined ||
			typeof window.axr_downloads[version][os] !== 'object')
		{
			alert('Sorry, but we could not find that');

			return;
		}

		var popup = $('#downloads__ask_arch');

		popup.find('span.version').html(version);
		popup.find('span.os').html(oses[os] || os);
		popup.find('div.options').empty();

		for (var arch in window.axr_downloads[version][os])
		{
			var a = $('<a>')
				.attr('href', window.axr_downloads[version][os][arch])
				.html(arches[arch] || arch);

			popup.find('div.options').append(a);
		}

		popup.show();
	});
})(jQuery);

window['App'].Rsrc.file('js/downloads.js').set_loaded();
