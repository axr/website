(function ()
{
	Core.Router.instance().on(/^\/get-involved$/, function ()
	{
		var $activity = $('#get_involved ._github_activity');

		Core.social.GitHubActivity.instance().get(function (events, error)
		{
			if (error)
			{
				$activity.html('<li><div class="inner">' + error.message + '</div></li>');
				return;
			}

			$activity.empty();

			for (var i = 0; i < 5; i++)
			{
				if (events[i] === undefined)
				{
					break;
				}

				$activity.append('<li><div class="inner">' + events[i].title + '</div></li>');
			}

			if (events.length === 0)
			{
				$activity.html('<li><div class="inner">No recent activity</div></li>');
			}
		});
	});
})();
