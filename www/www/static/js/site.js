$(document).ready(function ()
{
	var offset = $('[data-hash]').not(function ()
	{
		return $(this).attr('data-hash') !== window.location.hash.replace(/^#/, '');
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

	// Set up GA queue
	window._gaq = window._gaq || [];
	window._gaq.push(['_setAccount', App.vars.ga_accounts['default']]);
	window._gaq.push(['_trackPageview']);

	Core.Router.instance().update(window.location.pathname);
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
