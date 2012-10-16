App.pageEvent.on('load', '/Special:MixedLogin', function ()
{
	if ($('#mixed_login form').length > 0)
	{
		openid.init('openid_url');
	}
});
