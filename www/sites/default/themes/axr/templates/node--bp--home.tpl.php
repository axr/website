<div id="home">
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
			<div class="slides">
				<iframe width="346" height="278" src="https://www.youtube.com/embed/QwLY2gYyTFE?wmode=transparent" frameborder="0" allowfullscreen></iframe>
				<?php /*<iframe src="http://www.slideshare.net/slideshow/embed_code/6829398?rel=0" width="346" height="278" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>*/ ?>
			</div>
		</div>
	</div>

	<div class="advantages clearfix">
		<div class="nested_0">
			<h2>Advantages:</h2>
			<ul class="clearfix">
				<li class="first">
					<span class="block_0"></span>
					<span class="text">Build websites faster and easier</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Modularize and reuse your styling objects</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Vector graphics, powerful effects, textures, etc</span>
				</li>
			</ul>
			<ul class="clearfix">
				<li class="first">
					<span class="block_0"></span>
					<span class="text">True semantic content</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Exactly the same rendering across all browsers</span>
				</li>
				<li>
					<span class="block_0"></span>
					<span class="text">Truly separate content from presentation</span>
				</li>
			</ul>
			<a href="/about/features" class="learn_more button_std go">
				<span class="header"></span>
				<span class="content">Learn more<span class="block_0"></span></span>
				<span class="footer"></span>
			</a>
		</div>
	</div>

	<div class="hss_features">
		<div class="nested_0">
			<div class="features_menu" id="hss_features_menu">
				<h2>Features of HSS</h2>
				<div class="header"></div>
				<ul class="content">
					<li class="selected" data-section-name="hierarchical">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Hierarchical
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="object_oriented">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Object oriented
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="modular">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Modular
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="expressions">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Expressions
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="functions">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Functions
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="references">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								References
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="filters">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Filters
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="structural_independence">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Structural Independence
							</span>
							<span class="footer"></span>
						</a>
					</li>
					<li data-section-name="layout">
						<a href="#">
							<span class="header"></span>
							<span class="content">
								Layout
							</span>
							<span class="footer"></span>
						</a>
					</li>
				</ul>
				<div class="footer"></div>
			</div>
			<div class="features_content" id="hss_features_content">
				<div class="selected" data-section-name="hierarchical">
					<p>Instead of writing longer and longer selector chains, the rules (the selectors + the block with the properties) can be nested inside each other. If the content in the XML file is a tree of elements, why not apply styles using a tree as well? It is both clearer and has better performance, since not all elements have to be matched against each selector.</p>
					<h3>CSS:</h3>
<code data-language="css">books { }
books > book{ }
books > book > cover { }</code>

					<h3>HSS:</h3>
<code data-language="hss">books {
	book {
		cover { }
	}
}</code>
				</div>
				<div data-section-name="object_oriented">
					<p>Instead of dealing with an ever growing, plain list of properties, in HSS you use objects that encapsulate a group of related values, just as objects encapsulate related functionality in traditional object oriented programming languages.</p>
					<h3>CSS:</h3>
<code data-language="css">selector
{
	border-size: 1px;
	border-color: #F00;
	border-style: solid;
}</code>

					<h3>HSS:</h3>
<code data-language="hss">selector
{
	border: @line {
		size: 1;
		color: #F00;
	};
}</code>
				</div>
				<div data-section-name="modular">
					<p>You can give any HSS object a name, and reuse it as needed. You can make "presets" and apply them to the selected object(s) and then even override any property as needed.</p>
					<p>For example:</p>
<code data-language="hss">@linearGradient boxBg
{
	startColor: #F;
	endColor: #0;
	endY: 100%;
}

@container box
{
	width: 150;
	height: 100;
	background: boxBg;
}

selector chain
{
	isA: box;
}</code>
				</div>
				<div data-section-name="expressions">
					<p>Anywhere a number is accepted as a value, you can also use an expression. This is especially useful when you want to mix fixed-width elements with fluid ones. For example, when you have a sidebar 150 point wide, how wide is the rest? Answer: 100% - 150.</p>
					<p>For example:</p>
<code data-language="hss">sidebar
{
	width: 150;
}

content
{
	width: 100% - 150;
}</code>
				</div>
				<div data-section-name="functions">
					<p>Functions like min(), max() or avg() help you control the dimensions of your elements in fluid environments, without the need for countless properties such as min-width or max-height in CSS. The function will always return the appropriate value depending on the parameters.</p>
					<p>For example:</p>
<code data-language="hss">selector
{
	//make it 20% the width of the parent, but at
	//least 150 points wide and at most 400 points
	width: min(150, max(400, 20%));
}</code>
				</div>
				<div data-section-name="references">
					<p>Many times it is very useful to be able to refer to a property of another element to do some calculations, for example. Imagine a site with a sidebar and content area, for example. You want to make the content area as wide as the whole page minus the sidebar, which is flexible. So you'd do something like this:</p>
<code data-language="hss">page
{
	sidebar
	{
		//like in the previous example
		width: min(150, max(400, 20%));
	}

	content
	{
		width: 100% - ref(width of sidebar);
	}
}</code>
				</div>
				<div data-section-name="filters">
					<p>Filters are like pseudo-selectors in CSS but with a completely overhauled selection system. Since HSS has scope, the selectors are read from left to right. You select elements based on the name, for example, and then you filter that selection down to the elements you really want. There are a lot of them to cover all the different needs, so check out the docs.</p>
<code data-language="hss">//selects the first of the elements named foo
foo:first { }
//selects the elements named foo that are first inside
//their parent
foo:firstChild { }
//selects bar elements, then return the 2nd, 4th, 6th, etc
bar:even { }
//selects baz elements whose width is greater than 500
baz(width > 500) { }
//selects qux elements whose title attribute start
//with "Welcome"
qux:[title:startsWith("Welcome")] { }</code>
				</div>
				<div data-section-name="structural_independence">
					<p>The structure of the content is intrinsic to its meaning, it should NEVER be altered just because of some stylistic requirements. Therefore, in HSS you can freely manipulate the content tree to fit whatever structure you need to achieve your visual layout.</p>
<code data-language="hss">root
{
	//wrap the element called "navigation" in a new
	//"header" element
	#wrap(navigation) header
	{
		//supposing myMenuPreset has been defined elsewhere
		navigation { isA: myMenuPreset; }
	}
	//wrap everything that is not "header" or "footer" in a
	//new "content" element
	#wrap(!(header, footer)) content
	{
		//change the order of the items
		#move element2 { /*etc*/ }
		#move element1 { /*etc*/ }
		#move element3 { /*etc*/ }
	}
	//create a new footer, if not already there
	#ensure footer
	{
		//create a new search box, assuming the object has been
		//defined elsewhere
		#new search { isA: searchBox; }
	}
}</code>
				</div>
				<div data-section-name="layout">
					<p>A new box model, combined with most of the other features in this list, provide an awesome new way of doing layout, more suited to how designers think. Instead of floating, pushing and otherwise bang your head against the wall when laying out your elements on the page, you use alignX and alignY to set alignment points. These will determine the position of the elements in the page, which will flow inside of their parents, balancing themselves out when more than one tries to align itself on that specific point. It sounds more complicated than it really is, so here come some examples:</p>
<code data-language="hss">//align foo to the right horizontally
foo { alignX: left; }
//align bar at the middle vertically
bar { alignY: middle; }
//place baz with it's right edge 30 points to the left
//of the center of its parent, without affecting other
//elements
baz
{
	anchorX: 100%;
	alignX: 50% - 30;
	flow: no;
}
//align all elements *inside* qux at the middle vertically,
//and lay them out from top to bottom instead of left
//to right
qux
{
	contentAlignY: middle;
	direction: ttb;
}</code>
				</div>
			</div>
		</div>
	</div>

	<div class="getit">
		<div class="nested_0 clearfix">
			<div class="releases">
				<h2>download</h2>
				<div class="nested_0">
					<?php
						$latest = null;

						if (function_exists('axrreleases_get_releases'))
						{
							$releases = axrreleases_get_releases(0, 1);
							$latest = count($releases) > 0 ? $releases[0] : null;
						}
					?>
					<?php if (is_object($latest)): ?>
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
						<a class="older" href="<?php echo Config::get('/shared/files_url'); ?>/prototype">looking for an older version?</a>
						<h3>What am I downloading?</h3>
						<p>This is a test app that runs on your computer, and
						already does many of the cool features of HSS! It
						includes tests and tutorials you can try out.</p>
					<?php else: ?>
						<strong>No downloads available for your operating system</strong>
					<?php endif; ?>
				</div>
				<a class="altos" href="/downloads">need other operating system?</a>
				<div class="block_0"></div>
				<div class="block_1"></div>
				<div class="block_2"></div>
				<div class="block_3"></div>
			</div>
			<div class="changes">
				<h2>Latest changes:</h2>
				<?php
					$changelog = null;

					if (function_exists('axrreleases_get_changelog_short'))
					{
						$changelog = axrreleases_get_changelog_short();
					}
				?>
				<?php if (is_object($latest) && is_array($changelog)): ?>
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
</div>

