<div id="container">
	<header>
		<a href="/" id="logo"><img src="/sites/default/themes/axr/images/logo.jpg" height="230" width="310" alt="AXR Logo" title="AXR Project" /></a>
		<div class="secondary">
			<?php if ($user->uid == 0): ?>
				<span class="register"><a href="/user/register">Register</a></span>
				<span class="login"><a href="/user/login">Login</a></span>
			<?php else: ?>
				<div class="user_menu">
					<span class="arrow"></span>
					<a class="user_link" href="/user/<?php echo $user->uid; ?>/"><span class="menu_title">User</span></a>
					<div class="dropdown">
						<a class="edit_account first" href="/user/<?php echo $user->uid; ?>/edit/">Edit account</a>
						<a class="wiki_profile" href="/wiki/User:<?php echo $user->name; ?>">Wiki profile</a>
						<a class="logout last" href="/user/logout">Logout</a>
					</div>
				</div>
			<?php endif; ?>
			<form action="/search/node" method="post" accept-charset="UTF-8">
				<input type="search" name="keys" placeholder="Search" autocomplete="off" />
				<input type="hidden" name="form_id" value="search_form" />
				<input type="hidden" name="form_token" value="<?php print drupal_get_token('search_form'); ?>" />
			</form>
		</div>
		<nav>
			<?php include(drupal_get_path('theme', 'axr') . '/menu.tpl.php'); ?>
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
		<?php if ($ajaxsite_page): ?>
			<noscript>Please enable JavaScript</noscript>
			<script>
				window.Ajaxsite = window.Ajaxsite || {};
				try { window.Ajaxsite.load_url(window.location.pathname); }
				catch (e) { window.Ajaxsite.autoloadWhenReady = true; }
			</script>
			<script>
				(function (callback)
				{
					window.Ajaxsite_onInit = window.Ajaxsite_onInit || []
					try { window.Ajaxsite.on_init(callback); }
					catch (e) { window.Ajaxsite_onInit.push(callback); }
				})(function ()
				{ <?php echo implode('', $ajaxsite_js); ?> });
			</script>
		<?php else: ?>
			<?php if (!$is_front && $breadcrumb): ?>
				<nav id="breadcrumb">
					<?php echo $breadcrumb; ?>
				</nav>
			<?php endif; ?>

			<?php echo isset($messages) ? $messages : ''; ?>

			<?php if (!$is_front): ?>
				<?php print render($tabs); ?>
			<?php endif; ?>

			<?php print render($page['content']); ?>
		<?php endif; ?>
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
			        <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHLwYJKoZIhvcNAQcEoIIHIDCCBxwCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAJr0HZaLT67TmLXT+hJWwHm8hJb7w7JoWtua3V8X6QzA+wZtOo36jRADRPRWw/riKCDonpHGQSpaYfD9jnlF9Die8w0VDe/GFaqqjego175xxKbA43UsF25uvgJ05vtZSr6dIYcO9WIRyv1367+YmW3YMRNCbDTLcLRZ0ccaqIcDELMAkGBSsOAwIaBQAwgawGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIgxuR9HwvpJyAgYg22L/tviaFwEwOXqVGN9vlUv/DZ2BpHPBvxzhNjztKLJjomw86TvIF34WE4LqZCvxK6uFVgv7vpl9mBLSbZjKZvICAGcTKvtyVnAxxwHEvUh/JgvAv6d3Gq4VESFF2ZE06BUzReOHi4BCEWD+Nn6ETU3o745ZGxsXMu+7m3gUfTQRgcPOpragAoIIDhzCCA4MwggLsoAMCAQICAQAwDQYJKoZIhvcNAQEFBQAwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMB4XDTA0MDIxMzEwMTMxNVoXDTM1MDIxMzEwMTMxNVowgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBR07d/ETMS1ycjtkpkvjXZe9k+6CieLuLsPumsJ7QC1odNz3sJiCbs2wC0nLE0uLGaEtXynIgRqIddYCHx88pb5HTXv4SZeuv0Rqq4+axW9PLAAATU8w04qqjaSXgbGLP3NmohqM6bV9kZZwZLR/klDaQGo1u9uDb9lr4Yn+rBQIDAQABo4HuMIHrMB0GA1UdDgQWBBSWn3y7xm8XvVk/UtcKG+wQ1mSUazCBuwYDVR0jBIGzMIGwgBSWn3y7xm8XvVk/UtcKG+wQ1mSUa6GBlKSBkTCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb22CAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCBXzpWmoBa5e9fo6ujionW1hUhPkOBakTr3YCDjbYfvJEiv/2P+IobhOGJr85+XHhN0v4gUkEDI8r2/rNk1m0GA8HKddvTjyGw/XqXa+LSTlDYkqI8OwR8GEYj4efEtcRpRYBxV8KxAW93YDWzFGvruKnnLbDAF6VR5w/cCMn5hzGCAZowggGWAgEBMIGUMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbQIBADAJBgUrDgMCGgUAoF0wGAYJKoZIhvcNAQkDMQsGCSqGSIb3DQEHATAcBgkqhkiG9w0BCQUxDxcNMTEwMzIxMjIyNTMyWjAjBgkqhkiG9w0BCQQxFgQU1X4llNRU5kyFmW9N7Y97Z0DOH50wDQYJKoZIhvcNAQEBBQAEgYAKKtK7XVJM9BQaxw2wGqVZnq5YbxC3xNTeXh3irVqPIyeXGF8D7zrYWdJZoem5PwQr+idzgyoH7dzdGh5DaSqOspKezUeWSl6f/k/Oa1ilV3bk3VmIDOlZ23DyaCzd+ZsNMwlCSpDjv8j4NIlInaACvdNXOngoDQU0BVajHIqHoA==-----END PKCS7-----			        ">
			        <a href="javascript:donation_form.submit()" title="If you'd like to see this project become a reality, please consider making a donation">Donate</a>
			     </form>

				 <ul class="follow_us">
					<li class="twitter"><a href="https://twitter.com/AXRProject/" title="Follow us on Twitter" target="_blank">Twitter</a></li>
					<li class="facebook"><a href="https://www.facebook.com/pages/AXRProject/120456481367465?sk=info" title="Friend us on Facebook" target="_blank">Facebook</a></li>
					<li class="vimeo"><a href="https://vimeo.com/AXRProject/" title="Watch our videos on Vimeo" target="_blank">Vimeo</a></li>
				 </ul>

				 <ul class="last_tweet">
					<li class="tweet_container">Loading last tweet...</li>
					<li class="follow_us"><span>Follow us on Twitter: <a href="https://twitter.com/AXRProject/">@AXRProject</a></span></li>
					<li class="bird"></li>
				 </ul>

				 <div class="participate">
					<h2>Participate</h2>
					<p>AXR is an open source project, for everyone to benefit from. If you want to help, please join the <a href="https://groups.google.com/group/axr-main/">mailing list</a> and tell us what you think should happen next.</p>
				 </div>

				<div class="copy"><p>The AXR Project | <a href="mailto:team@axr.vg">team@axr.vg</a> | &copy; 2010 - <?php echo date("Y"); ?></p></div>
		</div>
	</footer>
</div>

