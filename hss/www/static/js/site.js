(function ()
{
	/**
	 * Get current object name
	 *
	 * @return {string}
	 */
	var current_object_name = function ()
	{
		var match = window.location.pathname.match(/^\/(@[a-zA-Z0-9]+)(\/|$)/);
		return (match || [])[1];
	};

	/**
	 * Expand collapse all objects
	 *
	 * @param bool collapse default: false
	 */
	var expand_all = function (collapse)
	{
		if (collapse === true)
		{
			$('#hssdoc_sidebar .obj_list > li').addClass('collapsed');
		}
		else
		{
			$('#hssdoc_sidebar .obj_list > li').removeClass('collapsed');
		}
	};

	$(window).on('hashchange load', function()
	{
		Core.site.scroll_to_hash();
	});

	$(document).ready(function ()
	{
		Core.site.on_ready();

		// Collapse all irrelevant objects on the sidebar
		$('#hssdoc_sidebar .obj_list > li').addClass('collapsed');
		$('#hssdoc_sidebar .obj_list > li[data-object="' + current_object_name() + '"]')
			.removeClass('collapsed')
			.parents('.obj_list > li').removeClass('collapsed');

		$('#hssdoc_sidebar .prop_list')
			.find('.ro, .implSemi, .implNone')
			.each(function (i, element)
			{
				$(element).tipsy({
					gravity: 'e'
				});
			});
	});

	/**
	 * Expand/collapse objects in sidebar
	 */
	$('#hssdoc_sidebar .obj_list > li a.open').on('click', function (e)
	{
		e.preventDefault();
		$(this).closest('.obj_list > li').toggleClass('collapsed');
	});

	$('#hssdoc_sidebar .actions a.expand').on('click', function (e)
	{
		e.preventDefault();
		expand_all();
	});

	$('#hssdoc_sidebar .actions a.collapse').on('click', function (e)
	{
		e.preventDefault();
		expand_all(true);
	});
})();

window._gaq = window._gaq || [];
window._gaq.push(['_setAccount', 'UA-20384487-1']);
window._gaq.push(['_trackPageview']);
Core.site.load_ga();
