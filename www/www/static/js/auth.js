App.pageEvent.on('load', '/auth', function ()
{
	if ($('#auth_login form').length > 0)
	{
		openid.init('identifier');
	}

	$('#auth_login_choice form a.create').on('click', function ()
	{
		$(this).closest('form').submit();
		return false;
	});
});

window['App'].Rsrc.file('js/auth.js').set_loaded();
