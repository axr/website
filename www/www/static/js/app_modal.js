window['App'] = window['App'] || {};

(function (App)
{
	/**
	 * Modal box system
	 */
	App.modal = {
		/**
		 * Has the modal system been initialized?
		 *
		 * @private
		 * @type {bool}
		 */
		_initialized: false,

		/**
		 * Initialize
		 */
		initialize: function ()
		{
			if (this._initialized)
			{
				return;
			}

			this._initialized = true;

			$('body').prepend(
				'<div id="as_modal">' +
					'<div id="as_modal_wrap">' +
						'<div class="inner">' +
							'<div class="content"></div>' +
						'</div>' +
					'</div>' +
				'</div>');
		},

		/**
		 * Show a modal box
		 *
		 * @param {string} html
		 * @param {Object<string, *>} options
		 */
		show: function (html, options)
		{
			var that = this;

			this.initialize();
			this.hide();

			options = options || {};

			if (!(options.size instanceof Array) ||
					options.size.length !== 2)
			{
				options.size = [500, 375];
			}

			$('#as_modal > div > .inner')
				.css('width', options.size[0] + 'px')
				.css('height', options.size[1] + 'px')
				.css('right', '-' + (options.size[0] / 2) + 'px')
				.css('bottom', '-' + (options.size[1] / 2) + 'px');
			$('#as_modal > div > .inner > .content').html(html);
			$('#as_modal').fadeIn(300);
		},

		/**
		 * Hide the modal box
		 */
		hide: function ()
		{
			$('#as_modal').fadeOut(300);
			$('#as_modal > div > .inner > .content').empty();
		}
	};
})(window['App']);

window['App'].Rsrc.file('js/app_modal.js').set_loaded();
