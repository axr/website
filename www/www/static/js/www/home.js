(function ()
{
	Core.Router.instance().once(/^\/$/, function ()
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

	Core.Router.instance().once(/^\/$/, function ()
	{
		Core.social.GitHubActivity.instance().get(function (events, error)
		{
			if (error)
			{
				$('#home > .social ._github_activity')
					.html('<li><div class="inner">' + error.message + '</div></li>');
				return;
			}

			$('#home > .social ._github_activity').empty();

			for (var i = 0; i < 5; i++)
			{
				if (events[i] === undefined)
				{
					break;
				}

				$('#home > .social ._github_activity')
					.append('<li><div class="inner">' + events[i].title + '</div></li>');
			}

			if (events.length === 0)
			{
				$('#home > .social ._github_activity')
					.html('<li><div class="inner">No recent activity</div></li>');
			}
		});
	});
})();
