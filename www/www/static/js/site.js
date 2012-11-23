window['App'] = window['App'] || {};

// Make the submenu items clickable in IE
// This probably won't be needed by the new menu
/*@cc_on@if(@_jscript_version<9)
window.attachEvent('onload',tab);
function tab() {
for(var lis=document.getElementById("menu").getElementsByTagName("li"),i=0;i<lis.length;i++){lis[i].getElementsByTagName("a")[0].id="time"+(i+1);if(lis[i].getElementsByTagName("div").length>0){for(var anchors=lis[i].getElementsByTagName("div")[0].getElementsByTagName("a"),j=0;j<anchors.length;j++){var a=anchors[j],t="time",href=a.href;a.setAttribute("begin",t+(i+1)+".focus");a.setAttribute("end",t+(i+2)+".focus;"+t+i+".focus");a.setAttribute(t+"Action","class:afocus");a.addBehavior("#default#time2");a.setAttribute("href",href)}}};
}
@end@*/

(function (App)
{
	App.Rsrc.bundle('js/bundle_rainbow.js').use(function ()
	{
		Rainbow.onHighlight(function (block)
		{
			var language = $(block).attr('data-language');
			var code = $(block).html().split('\n');
			var lines = [];

			for (var i = 0, c = code.length; i < c; i++)
			{
				lines.push({
					number: i,
					line: code[i].replace('\t', '    ') + '\n'
				});
			}

			// If the first line is empty, remove it
			if (lines[0].line.replace(/\s+/, '').length === 0)
			{
				lines.splice(0, 1);
			}

			// If the last line is empty, remove it
			if (lines[lines.length - 1].line.replace(/\s+/, '').length === 0)
			{
				lines.splice(lines.length - 1, 1);
			}

			App.data.template('code_frame', function (template)
			{
				var html = Mustache.render(template, {
					language: language,
					lines: lines
				});

				if ($(block).parent().prop('tagName') === 'PRE')
				{
					$(block).parent().replaceWith(html);
				}
				else
				{
					$(block).replaceWith(html);
				}
			});
		});

		$(document).ready(function ()
		{
			Rainbow.color();
		});
	});

	/**
	 * Dropdown menu
	 * @fixme Shouldn't this be implemented in CSS?
	 */
	$('#container > header > nav > ul > li').hover(function ()
	{
		$(this).closest('li').addClass('hover').find(".sections")
			.removeClass('hidden');
	}, function ()
	{
		$(this).closest('li').removeClass('hover').find(".sections")
			.addClass('hidden');
	});

	/**
	 * Dropdown for user menu
	 */
	$('#container > header div.user_menu').hover(function ()
	{
		$(this).addClass('hover').find('.dropdown').removeClass('hidden');
	}, function ()
	{
		$(this).removeClass('hover').find('.dropdown').addClass('hidden');
	});

	/**
	 * Back to top link animation
	 */
	$('#container > footer > a').click(function (event)
	{
		event.preventDefault();

		$('html, body').animate({
			scrollTop: 0
		}, 800);
	});

	App.data.lastTweetForBox(function (tweet, error)
	{
		$('#container > footer .last_tweet .tweet_container')
			.html(tweet || error);
	});

	/**
	 * data-hash
	 */
	{
		var hash = window.location.hash.replace(/^#/, '');
		var offset = $('*').filter(function ()
		{
			return $(this).attr('data-hash') === hash;
		}).offset();

		if (offset !== null && !isNaN(offset.top))
		{
			$('html, body').animate({
				scrollTop: offset.top
			}, 800);
		}
	}
})(window['App']);

window['App'].Rsrc.file('js/site.js').set_loaded();
