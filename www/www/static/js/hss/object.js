window['App'] = window['App'] || {};

(new Rsrc.File('js/hss/object.js')).provide(function ()
{
	App.HSS = App.HSS || {};
	App.HSS.Object = {
		/**
		 * Scroll to property
		 *
		 * @param string property
		 */
		scroll_to_property: function (property)
		{
			$('#hssdoc_obj section.prop_item').each(function (i, element)
			{
				if ($(element).attr('data-property') === property)
				{
					$('html, body').animate({
						scrollTop: $(element).offset().top
					}, 800);
				}
			});
		}
	};

	App.Event.on('App.HSS.Object.load', function ()
	{
		// Scroll to the property that's in the hash
		App.HSS.Object.scroll_to_property(window.location.hash.replace(/^#/, ''));
	});
});
