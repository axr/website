window['App'] = window['App'] || {};

(new Rsrc.File('js/site.js')).provide(function ()
{
	$(document).ready(function ()
	{
		App.Util.scroll_to_hash(window.location.hash.replace(/^#/, ''));

		App.Twitter.get_last_tweet(function (tweet, error)
		{
			$('#container > footer .last_tweet .tweet_container')
				.html(tweet || error.to_s());
		});
	});
});
