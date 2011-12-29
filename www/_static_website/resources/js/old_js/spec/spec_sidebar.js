$(function(){
	$('#spec > .spec_sidebar .chapters .tree .row').bind('selected', function(e){
		document.location = $(this).find('.link.name a').attr('href')
		
	});
});