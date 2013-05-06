(function ()
{
	Core.Router.instance().on(/^\/get-involved\/?$/, function ()
	{
		$('#get_involved .join_us a.join').on('click', function (e)
		{
			e.preventDefault();

			var html = $('#get_involved > script[data-template="join_us"]').text();

			(new Core.Modal(html)).show({
				size: [640, 550]
			});
		});
	});

	Core.Router.instance().on(/^\/get-involved\/?$/, function ()
	{
		Core.social.LastTweet.instance().get(function (tweet, error)
		{
			$('#get_involved ._last_tweet').html(tweet || error);
		});

		Core.social.GitHubActivity.instance().get(function (events, error)
		{
			if (error)
			{
				$('#ghactivity')
					.html('<li><div class="inner">' + error.message + '</div></li>');
				return;
			}

			$('#ghactivity').empty();

			for (var i = 0; i < 5; i++)
			{
				if (events[i] === undefined)
				{
					break;
				}

				$('#ghactivity')
					.append('<li><div class="inner">' + events[i].title + '</div></li>');
			}

			if (events.length === 0)
			{
				$('#ghactivity')
					.html('<li><div class="inner">No recent activity</div></li>');
			}
		});
	});
})();
