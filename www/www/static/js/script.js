// Menu accessibility: keyboard navigation for ≤ IE8 //
/*@cc_on@if(@_jscript_version<9)
for(var lis=document.getElementById("menu").getElementsByTagName("li"),i=0;i<lis.length;i++){lis[i].getElementsByTagName("a")[0].id="time"+(i+1);if(lis[i].getElementsByTagName("div").length>0){for(var anchors=lis[i].getElementsByTagName("div")[0].getElementsByTagName("a"),j=0;j<anchors.length;j++){var a=anchors[j],t="time";a.setAttribute("begin",t+(i+1)+".focus");a.setAttribute("end",t+(i+2)+".focus;"+t+i+".focus");a.setAttribute(t+"Action","class:afocus");a.addBehavior("#default#time2")}}};
@end@*/

(function ($)
{
	/**
	 * Load GitHub activity
	 */
	var loadGHActivity = function ()
	{
		/**
		 * Load GitHub activity for the activity box
		 */
		$.getJSON('/activity.php', function (data)
		{
			$('#ghactivity').empty();

			for (var i = 0; i < 5; i++)
			{
				if (data.activity[i] === undefined)
				{
					break;
				}

				var item = data.activity[i];

				$('#ghactivity')
					.append('<li><div class="inner">' + item.title + '</div></li>');
			}
		});
	};

	var insertCodeFrame = function (block)
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

		Ajaxsite.data.template('code_frame', function (template)
		{
			$(block).replaceWith(Mustache.render(template, {
				language: language,
				lines: lines
			}));
		});
	}

	/**
	 * Dropdown menu
	 * @fixme Shouldn't this be implemented in CSS?
	 */
	$("#container > header > nav > ul > li").hover(function ()
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
	$("#container > header > .secondary > div.user_menu").hover(function ()
	{
		$(this).addClass('hover').find(".dropdown").removeClass('hidden');
	}, function ()
	{
		$(this).removeClass('hover').find(".dropdown").addClass('hidden');
	});

	/**
	 * Show popup for the flaoting social buttons
	 */
	$("#container > .share > ul > li > a").click(function (event)
	{
		event.preventDefault();

		var page = $(this).attr("href");
		var popUpHeight = 245;
		var popUpWidth = 730;
		var top = ($(window).height() - popUpHeight) / 2;
		var left = ($(window).width() - popUpWidth) / 2;
		var options = 'toolbar=no, location=no, directories=no, status=no, ' +
			'menubar=no, scrollbars=no, resizable=yes, width=' + popUpWidth +
			', height=' + popUpHeight + ', top=' + top + ', left=' + left;
		window.open(page, "", options);
	});

	/**
	 * Back to top link animation
	 */
	$("#container > footer > a").click(function (event)
	{
		event.preventDefault();

		$('html, body').animate({
			scrollTop: 0
		}, 800);
	});

	/**
	 * Handle search form
	 */
	$('header > .secondary > form').on('submit', function (e)
	{
		e.preventDefault();

		var keys = $(this).find('input[type=search]').val();
		var type = /^\/wiki\//.test(window.location.pathname) ? 'wiki' : 'mixed';
		var url = '/search/' + type + '/' + encodeURIComponent(keys);

		if (typeof Ajaxsite === 'undefined')
		{
			window.location = url;
		}
		else
		{
			Ajaxsite.url(url);
		}
	});

	$(document).ready(function ()
	{
		withAS(function ()
		{
			Ajaxsite.data.lastTweetForBox(function (tweet, error)
			{
				$('#container > footer .last_tweet .tweet_container')
					.html(tweet || error);
			});

			Rainbow.onHighlight(function (block)
			{
				insertCodeFrame(block);
			});
		});

		if (/^\/get-involved(\/|$)/.test(window.location.pathname))
		{
			loadGHActivity();
		}
	});
})(jQuery);

