<?php
	$root = defined('DRUPAL_ROOT') ? '' : 'http://axr.vg';
?>
<ul id="menu">
	<li class="about">
		<span class="arrow"></span>
		<a href="<?php echo $root; ?>/about/features">About</a>
		<div>
			<a class="features first" href="<?php echo $root; ?>/about/features">Features</a>
			<a class="manifesto last" href="<?php echo $root; ?>/about/manifesto">Manifesto</a>
			<!-- <a class="media_kit" href="<?php echo $root; ?>/under-construction">Media Kit</a> -->
			<!-- <a class="history" href="<?php echo $root; ?>/under-construction">History</a> -->
		</div>
	</li>
	<li class="specification">
		<a href="<?php echo $root; ?>/spec">Specification</a>
		</li>
	<li class="resources">
		<span class="arrow"></span>
		<a href="<?php echo $root; ?>/downloads">Resources</a>
		<div>
			<a class="downloads first" href="<?php echo $root; ?>/downloads">Downloads</a>
			<!-- <a class="examples" href="<?php echo $root; ?>/under-construction">Examples</a> -->
			<!-- <a class="tutorials" href="<?php echo $root; ?>/under-construction">Tutorials</a> -->
			<a class="documentation last" href="http://docs.axr.vg">Prototype documentation</a>
		</div>
	</li>
	<li class="community">
		<span class="arrow"></span>
		<a href="<?php echo $root; ?>/get-involved">Community</a>
		<div>
			<a class="get_involved first" href="<?php echo $root; ?>/get-involved">Get involved</a>
			<a class="chat" href="http://webchat.freenode.net/?channels=axr" target="_blank">Chat</a>
			<!-- <a class="forum" href="<?php echo $root; ?>/under-construction">Forum</a> -->
			<a class="github last" href="https://github.com/AXR" target="_blank">GitHub</a>
		</div>
	</li>
	<li class="wiki">
		<span class="arrow"></span>
		<a href="<?php echo $root; ?>/wiki">Wiki</a>
		<div>
		    <a class="main first" href="<?php echo $root; ?>/wiki">Main page</a>
			<a class="faq" href="<?php echo $root; ?>/wiki/FAQ">FAQ</a>
			<a class="roadmap" href="<?php echo $root; ?>/wiki/Roadmap">Roadmap</a>
			<a class="changelog last" href="<?php echo $root; ?>/wiki/Changelog">Changelog</a>
		</div>
	</li>
	<li class="blog"><a href="<?php echo $root; ?>/blog">Blog</a></li>
</ul>

