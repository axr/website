window['App'] = window['App'] || {};

(new Rsrc.File('js/wiki/site.js')).provide(function ()
{
	$(document).ready(function ()
	{
		App.GoogleAnalytics.initialize(App.vars.ga_accounts['default']);
		App.GoogleAnalytics.queue(['_trackPageview']);
	});
});
