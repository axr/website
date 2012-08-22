App.pageEvent.on('load', '/get-involved', function ()
{
	App.data.lastTweetForBox(function (tweet, error)
	{
		$('#get_involved .box.twitter ._last_tweet').html(tweet || error);
	});

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

