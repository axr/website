<div class="above_fold">
	<div class="nested_0 clearfix">
		<div class="intro">
			<h2>AXR stands for Arbitrary XML Rendering. It's aim is to provide a better alternative to HTML+CSS</h2>
			<p>It uses XML for the content and HSS for the design and simple behavior of the interface. HSS  is a language based on CSS, but offers many more advanced features, such as object orientation, rule nesting, expressions, references to other objects, modularization (code reuse), etc. JavaScript will be used as well for advanced behavior.</p>
			<a href="/get-involved" class="join button_std">
				<span class="header"></span>
				<span class="content">
					<span class="block_0"></span>
					<span class="big">become a volunteer</span>
					<span class="small">join the revolution</span>
				</span>
				<span class="footer"></span>
			</a>
			<a href="/about/manifesto" class="manifesto button_std gray">
				<span class="header"></span>
				<span class="content">
					<span class="block_0"></span>
					<span>read the manifesto</span>
				</span>
				<span class="footer"></span>
			</a>
		</div>
		<div class="slides"><iframe src="http://www.slideshare.net/slideshow/embed_code/6829398?rel=0" width="346" height="278" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe></div>
	</div>
</div>

<div class="advantages clearfix">
	<div class="nested_0">
		<h2>Advantages:</h2>
		<ul class="clearfix">
			<li>
				<span class="block_0"></span>
				<span class="text">Build websites faster and easier</span>
			</li>
			<li>
				<span class="block_0"></span>
				<span class="text">Separate content from presentation</span>
			</li>
			<li>
				<span class="block_0"></span>
				<span class="text">Modularize and reuse your styling objects</span>
			</li>
		</ul>
		<ul class="clearfix">
			<li>
				<span class="block_0"></span>
				<span class="text">Exactly the same rendering across all browsers</span>
			</li>
			<li>
				<span class="block_0"></span>
				<span class="text">Vector graphics, powerful effects, textures, etc</span>
			</li>
			<li>
				<span class="block_0"></span>
				<span class="text">True semantic content</span>
			</li>
		</ul>
		<a href="/about/features" class="learn_more button_std go">
			<span class="header"></span>
			<span class="content">Learn more<span class="block_0"></span></span>
			<span class="footer"></span>
		</a>
	</div>
</div>

<div class="getit">
	<div class="nested_0 clearfix">
		<div class="releases">
			<h2>download</h2>
			<div class="nested_0">
				<?php
					$releases = axr_get_releases(0, 1);
					$latest = count($releases) > 0 ? $releases[0] : null;
				?>
				<?php if ($latest !== null): ?>
					<a href="<?php echo $latest->url; ?>" class="download button_big">
						<span class="header"></span>
						<span class="content">
							<span class="big">Latest version:</span>
							<span class="version">
								<?php echo $latest->version; ?> prototype
							</span>
							<span class="for">for <?php echo $latest->os_str; ?></span>
							<span class="block_0"></span>
						</span>
						<span class="footer"></span>
					</a>
				<?php else: ?>
					<strong>No downloads available for your operating system</strong>
				<?php endif; ?>

				<?php $olds = axr_get_releases(1, 3); ?>
				<?php if (count($olds) > 0): ?>
					<div class="olds">older releases</div>
					<ul>
						<?php foreach ($olds as $release): ?>
							<li>
								<span class="version">v <?php echo $release->version; ?></span>
								<span class="date"><?php echo $release->date; ?></span>
								<a class="download" href="<?php echo $release->url; ?>">Download</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<a class="altos" href="/resources/downloads">need other operating system?</a>
			<div class="block_0"></div>
			<div class="block_1"></div>
			<div class="block_2"></div>
			<div class="block_3"></div>
		</div>
		<div class="changes">
			<h2>Latest changes:</h2>
			<?php $changelog = axr_get_changelog_short('2368d33f1ae0fd15a9866da0b32dd98ebab49e67'); ?>
			<?php if ($latest !== null && $changelog !== null): ?>
				<div class="verinfo">
					<span class="version">v <?php echo $latest->version; ?></span>
					Released: <?php echo $latest->date; ?>
				</div>
				<ul>
					<?php foreach ($changelog as $change): ?>
						<li>
							<span class="block_0"></span>
							<span class="text"><?php echo $change; ?></span>
						</li>
					<?php endforeach; ?>
					<li class="clear"></li>
				</ul>

				<a href="/wiki/changelog" class="see_all button_std go">
					<span class="header"></span>
					<span class="content"><span class="block_0"></span>See all</span>
					<span class="footer"></span>
				</a>
			<?php else: ?>
				<div class="verinfo">
					No changelog is currently available
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!--<div class="the_cool_stuff">
	<div class="nested_0">
		<div class="downloads">
			<div class="download buttons">
				<a class="latest" href="prototype.dmg"><span class="label">download</span><span class="version">0.42 prototype</span></a>
				<a class="older" href="older_versions.html">older versions</a>
			</div>
			<div class="download info">
				<h2>System Requirements</h2>
				<p>Right now the prototype requires Mac OS X 10.6 or newer, on a 64bit Intel Mac. Support for Windows and Linux will come soon.</p>
			</div>
		</div>
		<div class="advantages">
			<h2>Advantages:</h2>
			<ul>
				<li><span class="block_0"></span><span>Build websites faster and easier</span></li>
				<li><span class="block_0"></span><span>Separate content from presentation</span></li>
				<li><span class="block_0"></span><span>Modularize and reuse your styling objects</span></li>
				<li><span class="block_0"></span><span>Exactly the same rendering across all browsers</span></li>
				<li><span class="block_0"></span><span>Vector graphics, powerful effects, textures, etc</span></li>
				<li><span class="block_0"></span><span>True semantic content</span></li>
			</ul>
			<a href="features.html" class="features button_std">
				<span class="header"></span>
				<span class="content">See all features<span class="block_0"></span></span>
				<span class="footer"></span>
			</a>
		</div>
	</div>
</div>-->

