(function($) {
	$(function(){
	
		beautifyTweet = function(tweet) {
			parseURL = function(tweet) {
				return tweet.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&\?\/.=]+/, function(url) {
					return url.link(url);
				});
			};
			
			parseUsername = function(tweet) {
				return tweet.replace(/[@]+[A-Za-z0-9-_]+/, function(u) {
					return u.link("http://twitter.com/"+u.replace("@",""));
				});
			};
			
			parseHashtag = function(tweet) {
				return tweet.replace(/[#]+[A-Za-z0-9-_]+/, function(t) {
					return t.link("http://search.twitter.com/search?q="+t.replace("#","%23"));
				});
			};
		
			tweet= parseURL(tweet);
			tweet= parseUsername(tweet);
			tweet= parseHashtag(tweet);
			
			return tweet; 
		};
		
		$.getJSON("http://api.twitter.com/1/statuses/user_timeline.json?count=1&include_rts=t&screen_name=axrproject&callback=?",
				function(tweet){
					$("#container > footer > .activity > .last_tweet > p:first").html(beautifyTweet(tweet[0].text));
				}
		);
	
		
		$("#container > header > nav > ul > li").hover(function(){
		    $(this).closest('li').addClass('hover').find(".sections").removeClass('hidden');
		}, function(){
		    $(this).closest('li').removeClass('hover').find(".sections").addClass('hidden');
		});
		
		
		$("#container > #intro > ul.social > li > a").click(function(event) {
			event.preventDefault();
			var page = $(this).attr("href");
			var popUpHeight = 245;
			var popUpWidth = 730;
			var top = ($(window).height() - popUpHeight) / 2;
			var left = ($(window).width() - popUpWidth) / 2;
			var options= "toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=yes, width=" + popUpWidth + ", height=" + popUpHeight + ", top= " + top + ", left=" + left;
			window.open(page,"",options);
		});
		
		$("#container > footer > a").click(function(event){
			event.preventDefault();
			$('html, body').animate({ scrollTop: 0 }, 800);
		});
	});
})(jQuery);