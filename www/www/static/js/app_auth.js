window['App'] = window['App'] || {};

(function (App)
{
	/**
	 * Auth manager
	 */
	App.Auth = {
		/**
		 * This should be done before using the auth component. Preferrably when
		 * the page is loaded
		 */
		initialize: function ()
		{
			window.addEventListener('message', function (event)
			{
				var data;

				var origin_url = $('<a>').attr('href', event.origin).get(0);
				var www_url = $('<a>').attr('href', App['/shared/www_url']).get(0);

				if (origin_url.hostname !== www_url.hostname)
				{
					return;
				}

				try
				{
					data = JSON.parse(event.data);
				}
				catch (e)
				{
					return;
				}

				$.ajax({
					url: App.site.aa_handler,
					method: 'GET',
					data: {
						www_sid: data.sid
					},
					dataType: 'json',
					success: function (data, text_status, jq_xhr)
					{
						if (data.autoauth.status === 0)
						{
							alert('You have been automatically logged in');
						}
					}
				});
			}, false);
		},

		/**
		 * Try to login automatically
		 */
		autoauth: function ()
		{
			if (App.session.is_logged === true)
			{
				Cookie.set('aa_done', '1');
				return;
			}

			if (Cookie.get('aa_done') === '1')
			{
				return;
			}

			Cookie.set('aa_done', '1');

			var frame = $('<iframe>')
				.attr('src', App['/shared/www_url'] + '/auth/ra_sid_frame' +
					'?app_id=' + encodeURIComponent(App.site.app_id) +
					'&respond_to=' + encodeURIComponent(window.location))
				.hide();

			$('body').append(frame);
		},
	};
})(window['App']);

window['App'].Rsrc.file('js/app_auth.js').set_loaded();
