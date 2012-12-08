App.pageEvent.on('load_init', '/home', function ()
{
	/**
	 * Handle the HSS Features menu
	 *
	 * @todo this code is *very* ugly. Make it better
	 */
	$('#hss_features_menu a').on('click', function (e)
	{
		e.preventDefault();

	    var section_name = $(this).closest('li').attr('data-section-name');
	    $('#hss_features_menu > .content > li.selected').removeClass('selected');
	    $('#hss_features_menu > .content > li[data-section-name='+section_name+']').addClass('selected');
        $('#hss_features_content > div.selected').removeClass('selected');
        $('#hss_features_content > div[data-section-name='+section_name+']').addClass('selected');
	});

	$('#home .getit a.uiButton.get').on('click', function (e)
	{
		window._gaq.push(['_trackEvent', 'Downloads', 'HomeBigButton', $(this).attr('data-filename')]);
	});
});

App.pageEvent.on('load', '/home', function ()
{
	App.data.githubActivity({ count: 5 }, function (events, error)
	{
		if (error instanceof App.Error)
		{
			$('#home > .social ._github_activity').html('<li><div class="inner">' +
				error.to_s() + '</div></li>');
			return;
		}

		$('#home > .social ._github_activity').empty();

		for (var i = 0, c = events.length; i < c; i++)
		{
			$('#home > .social ._github_activity').append('<li><div class="inner">' +
				events[i].title + '</div></li>');
		}
	});
});

window['App'].Rsrc.file('js/home.js').set_loaded();
