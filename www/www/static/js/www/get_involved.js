(new Rsrc.File('js/www/get_involved.js')).provide(function ()
{
	App.WWW = App.WWW || {};

	App.WWW.GetInvolved = {
		_initialized: false,

		initialize: function ()
		{
			if (this._initialized === true)
			{
				return;
			}

			this._initialized = true;

			$('#get_involved .join_us a.join').on('click', function (e)
			{
				e.preventDefault();

				(new App.Template('get_involved_join')).request(function (template, error)
				{
					if (error instanceof App.Error)
					{
						error.show();
						return;
					}

					(new App.Modal(template)).show({
						size: [640, 550]
					});
				});
			});
		}
	};

	App.Event.on('App.WWW.GetInvolved.load', function ()
	{
		App.WWW.GetInvolved.initialize();

		App.Twitter.get_last_tweet(function (tweet, error)
		{
			$('#get_involved ._last_tweet')
				.html(tweet || error.to_s());
		});

		App.GitHub.get_activity(function (events, error)
		{
			if (error instanceof App.Error)
			{
				$('#ghactivity').html(error.to_s());
				return;
			}

			$('#ghactivity').empty();

			for (var i = 0; i < 5; i++)
			{
				if (events[i] === undefined)
				{
					break;
				}

				$('#ghactivity').append('<li><div class="inner">' +
					events[i].title + '</div></li>');
			}
		});
	});
});
