window.App = window.App || {};

(function (App)
{
	/**
	 * Get current object name
	 *
	 * @return string
	 */
	var getObjectName = function ()
	{
		return window.location.pathname.match(/^\/doc\/([^\/]+)/)[1];
	};

	/**
	 * Scroll to property
	 *
	 * @param string property
	 */
	var scrollToProperty = function (property)
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
	};

	App.pageEvent.on('load', '/doc', function ()
	{
		// Scroll to the property that's in the hash
		scrollToProperty(window.location.hash.replace(/^#/, ''));

		// Expand the current object in the sidebar
		$('#hssdoc_sidebar .obj_list > li').each(function (i, element)
		{
			if ($(element).attr('data-object') === getObjectName())
			{
				$(element).find('.prop_list').show();
				$(element).find('a.open').html('[-]');
			}
		});
	});

	App.pageEvent.on('load_init', '/doc', function ()
	{
		$('#hssdoc_sidebar .obj_list > li a.open').on('click', function (e)
		{
			e.preventDefault();

			var el = $(this).parent().parent().find('.prop_list').toggle();
			$(this).html(el.is(':visible') ? '[-]' : '[+]');
		});

		$('#hssdoc_sidebar .obj_list .prop_list a').on('click', function (e)
		{
			var targetObject = $(this).closest('ul.prop_list')
				.parent().attr('data-object');
			var targetProperty = $(this).attr('href').match(/^.+?#(.+)$/)[1];

			if (targetObject === getObjectName())
			{
				scrollToProperty(targetProperty);
			}
		});
	});
})(window.App);

