App.pageEvent.on('load', '/get-involved', function ()
{
	App.data.lastTweetForBox(function (tweet, error)
	{
		$('#get_involved .box.twitter ._last_tweet').html(tweet || error);
	});

	$.getJSON('/_ajax/ghactivity?count=5', function (data)
	{
		var events = data.payload.events;

		$('#ghactivity').empty();

		for (var i = 0, c = events.length; i < c; i++)
		{
			$('#ghactivity').append('<li><div class="inner">' +
				events[i].title + '</div></li>');
		}
	});
});

App.pageEvent.on('load_init', '/get-involved', function ()
{
	$('#get_involved .join_us a.join').on('click', function (e)
	{
		e.preventDefault();

		App.data.template('get_involved_join', function (template, error)
		{
			if (error)
			{
				App.error(error);
				return;
			}

			App.modal.show(template, {
				size: [640, 550]
			});
		});
	});
});

