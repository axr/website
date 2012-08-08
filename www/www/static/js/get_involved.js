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

		if (this.data._prerendered !== true)
		{
			this.render();
		}

		if (!this.isInitialized)
		{
			jQuery('#get_involved .join_us a.join').on('click', function (e)
			{
				jQuery('#get_involved .modal._joining').trigger('show');
				return false;
			});
		}
	};
})(Ajaxsite);

