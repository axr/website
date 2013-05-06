/**
 * Dropdown menu
 * @fixme Shouldn't this be implemented in CSS?
 */
$('#container > header > nav > ul > li').on('hover', function ()
{
	$(this).closest('li')
		.addClass('hover')
		.find('.sections')
		.removeClass('hidden');
}, function ()
{
	$(this).closest('li')
		.removeClass('hover')
		.find('.sections')
		.addClass('hidden');
});

/**
 * Back to top link animation
 */
$('#container > footer .back_to_top').on('click', function (event)
{
	event.preventDefault();

	$('html, body').animate({
		scrollTop: 0
	}, 800);
});
