$(document).ready(function ()
{
	var hash = decodeURIComponent(window.location.hash.replace(/^#/, ''));
	var offset = $('[data-hash]').not(function ()
	{
		return $(this).attr('data-hash') !== hash;
	}).offset();

	if (offset !== null && !isNaN((offset || {}).top))
	{
		$('html, body').animate({
			scrollTop: offset.top
		}, 800);
	}

	Core.social.LastTweet.instance().get(function (tweet, error)
	{
		$('#container > footer ._last_tweet')
			.html(tweet || error.message);
	});

	Core.CodeBox.find_all(document.body);

	if (typeof App === 'object' &&
		typeof App.vars === 'object' &&
		typeof App.vars.ga_account === 'object')
	{
		// Set up GA queue
		window._gaq = window._gaq || [];
		window._gaq.push(['_setAccount', App.vars.ga_accounts['default']]);
		window._gaq.push(['_trackPageview']);
	}

	Core.Router.instance().update(Core.Router.instance().url(window.location));
});

// Load GA tracker code
(function ()
{
	var ga = document.createElement('script');
	ga.type = 'text/javascript';
	ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(ga, s);
})();
