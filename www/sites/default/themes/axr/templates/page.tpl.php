<div id="container">
	<header>
		<a href="/" id="logo">AXR Project</a>
		<div class="secondary">
			<?php if ($user->uid == 0): ?>
				<a class="login" href="/user/login"><span class="extra_0"></span><span class="extra_1">Login</span></a>
			<?php endif; ?>
			<form action="/search/node" method="post" accept-charset="UTF-8">
				<span class="search">
					<input type="text" name="keys" value="Search" />
					<input type="submit" value="Search" />
				</span>
			</form>
		</div>
		<nav>
			<ul id="menu">
				<li class="about">
					<span class="arrow"></span>
					<a href="/under-construction">About</a>
					<div>
						<a class="features first" href="/under-construction">Features</a>
						<a class="manifesto" href="/under-construction">Manifesto</a>
						<a class="medi_kit" href="/under-construction">Media Kit</a>
						<a class="history last" href="/under-construction">History</a>
					</div>
				</li>
				<li class="specification">
					<a href="http://spec.axr.vg/specification.html">Specification</a>
					</li>
				<li class="resources">
					<span class="arrow"></span>
					<a href="/under-construction">Resources</a>
					<div>
						<a class="downloads first" href="/">Downloads</a>
						<a class="examples" href="/under-construction">Examples</a>
						<a class="tutorials" href="/under-construction">Tutorials</a>
						<a class="documentation last" href="/under-construction">Documentation</a>
					</div>
				</li>
				<li class="community">
					<span class="arrow"></span>
					<a href="/get-involved">Community</a>
					<div>
						<a class="get_involved first" href="/get-involved">Get involved</a>
						<a class="chat" href="http://webchat.freenode.net/?channels=axr">Chat</a>
						<a class="forum" href="/under-construction">Forum</a>
						<a class="github last" href="https://github.com/AXR">GitHub</a>
					</div>
				</li>
				<li class="wiki">
					<span class="arrow"></span>
					<a href="/under-construction">Wiki</a>
					<div>
						<a class="faq first" href="/under-construction">FAQ</a>
						<a class="roadmap" href="/under-construction">Roadmap</a>
						<a class="changelog last" href="/under-construction">Changelog</a>
					</div>
				</li>
				<li class="blog"><a href="/blog">Blog</a></li>
			</ul>
		</nav>
	</header>
	<div class="fork_github"><a href="https://github.com/AXR/Prototype" target="_blank">Fork me on GitHub</a></div>
	<div class="share">
		<p class="label">Share me</p>
		<ul>
			<li class="twitter"><a href="https://twitter.com/intent/tweet?text=AXR%3A%20The%20web%2C%20done%20right%20%23axr%20%7C%20http%3A%2F%2Faxr.vg%20by%20%40AXRProject" title="Share this project on Twitter!">Twitter</a></li>
			<li class="facebook"><a href="https://facebook.com/sharer.php?u=http%3A%2F%2Faxr.vg" title="Share this project on Facebook!">Facebook</a></li>
			<li class="delicious"><a href="https://del.icio.us/posts/add?url=http%3A%2F%2Faxr.vg%2F&description=AXR%3A%20The%20web%2C%20done%20right" target='_blank' title="Delicious">Delicious</a></li>
		</ul>
	</div>
	<div id="main" role="main">
		<?php if (!$is_front && $breadcrumb): ?>
			<nav id="breadcrumb">
				<?php echo $breadcrumb; ?>
			</nav>
		<?php endif; ?>

		<?php print render($page['content']); ?>
	</div>

	<footer>
		<a href="#top" title="Back to top">Back to top</a>
		<ul class="technologies_used">
			<li class="html5"><a href="https://www.w3.org/html/logo/" title="HTML5">HTML5</a></li>
			<li class="humanstxt"><a href="http://axr.vg/humans.txt" title="humans.txt">humans.txt</a></li>
			<li class="github"><a href="https://github.com/AXR/" title="AXR Project on GitHub">GitHub</a></li>
			<li class="google_groups"><a href="https://groups.google.com/group/axr-main/" title="join the mailing list">Google Groups</a></li>
		</ul>

		<div class="activity">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" name="donation_form">
			        <input type="hidden" name="cmd" value="_s-xclick">
			        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----			        ">
			        <a href="javascript:donation_form.submit()" title="If you'd like to see this project become a reality, please consider making a donation">Donate</a>
			     </form>

				 <ul class="follow_us">
					<li class="twitter"><a href="https://twitter.com/AXRProject/" title="Follow us on Twitter" target="_blank">Twitter</a></li>
					<li class="facebook"><a href="https://www.facebook.com/pages/AXRProject/120456481367465?sk=info" title="Friend us on Facebook" target="_blank">Facebook</a></li>
					<li class="vimeo"><a href="https://vimeo.com/AXRProject/" title="Watch our videos on Vimeo" target="_blank">Vimeo</a></li>
				 </ul>

				 <div class="last_tweet">
					<p class="tweet_container">Loading last tweet...</p>
					<p class="follow_us">Follow us on Twitter: <a href="https://twitter.com/AXRProject/">@AXRProject</a></p>
				 </div>

				 <div class="participate">
					<h2>Participate</h2>
					<p>AXR is an open source project, for everyone to benefit from. If you want to help, please join the <a href="https://groups.google.com/group/axr-main/">mailing list</a> and tell us what you think should happen next.</p>
				 </div>

				<div class="copy"><p>The AXR Project | <a href="mailto:team@axr.vg">team@axr.vg</a> | &copy; 2010 - 2011</p></div>
		</div>
	</footer>
</div>

