App.pageEvent.on('load', '/home', function ()
{
	/**
	 * Handle the HSS Features menu
	 *
	 * @todo this code is *very* ugly. Make it better
	 */
	$('#hss_features_menu a').on('click', function (e)
	{
		e.preventDefault();

	    var section_name = $(this).closest('li').attr('data-section-name');
	    $('#hss_features_menu > .content > li.selected').removeClass('selected');
	    $('#hss_features_menu > .content > li[data-section-name='+section_name+']').addClass('selected');
        $('#hss_features_content > div.selected').removeClass('selected');
        $('#hss_features_content > div[data-section-name='+section_name+']').addClass('selected');
	});
});

