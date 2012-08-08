(function (Ajaxsite)
{
	Ajaxsite.handlers.get_involved = function (data)
	{
		var that = this;

		/**
		 * Data
		 *
		 * @var object
		 */
		this.data = data;

		/**
		 * Has the page been initialized (event handlers bound)
		 *
		 * @var bool
		 */
		this.isInitialized = false;

		/**
		 * Render the page
		 */
		this.render = function ()
		{
			Ajaxsite.data.sp_forajax('/get-involved', function (data, error)
			{
				if (error)
				{
					Ajaxsite.error('Error while loading /get-involved: ' + error, true);
					return;
				}

				Ajaxsite.content.html(data.html);
			});
		};

		if (!this.data._prerendered)
		{
			this.render();
		}

		if (!this.isInitialized)
		{
			// Preload it
			Ajaxsite.data.template('get_involved_join');

			jQuery('#get_involved .join_us a.join').on('click', function (e)
			{
				e.preventDefault();

				Ajaxsite.data.template('get_involved_join',
					function (template, error)
				{
					if (error)
					{
						Ajaxsite.error('Error loading the dialog: ' +
							error, true);
						return;
					}

					Ajaxsite.modal.show(template, {
						size: [640, 550]
					});
				});
			});

			this.isInitialized = true;
		}
	};
})(Ajaxsite);

