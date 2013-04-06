window['App'] = window['App'] || {};

(new Rsrc.File('js/wiki/site.js')).provide(function ()
{
	$(document).ready(function ()
	{
		App.GoogleAnalytics.initialize(App.vars.ga_accounts['default']);
		App.GoogleAnalytics.queue(['_trackPageview']);

		(new Rsrc.File('js/code_box.js')).request(function (error)
		{
			App.CodeBox.find_all(document.body);
		});
	});
});
