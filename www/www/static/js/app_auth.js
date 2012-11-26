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
							App.Auth.show_aa_bar(data.autoauth.payload);
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

		/**
		 * Show the autoauth notification bar
		 *
		 * @param {Object} data
		 */
		show_aa_bar: function (data)
		{
			App.data.template('autoauth_bar', function (template)
			{
				var bar = $(Mustache.render(template, data));

				bar.on('click', 'a._close', function (e)
				{
					$('body > .aa_bar').fadeOut(200);

					setTimeout(function ()
					{
						$('body > .aa_bar').remove();
					}, 200);
				});

				bar.on('click', 'a._reload', function (e)
				{
					window.location.reload(false);
				});

				$('body').prepend(bar);
			});
		}
	};
})(window['App']);

window['App'].Rsrc.file('js/app_auth.js').set_loaded();
