<!doctype html>
<!--[if lt IE 7]> <html class="ie6" lang="en" itemscope itemtype="http://schema.org/Organization"> <![endif]-->
<!--[if IE 7]>    <html class="ie7" lang="en" itemscope itemtype="http://schema.org/Organization"> <![endif]-->
<!--[if IE 8]>    <html class="ie8" lang="en" itemscope itemtype="http://schema.org/Organization"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en" itemscope itemtype="http://schema.org/Organization"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<?php print $head; ?>
	<!-- general meta-->
	<title><?php print $head_title; ?> | AXR Project</title>
	<meta name="description" content="AXR stands for Arbitrary XML Rendering. Its aim is to provide a better alternative to HTML+CSS">
	<meta name="author" content="Miro Keller">
	<link rel="canonical" href="http://axr.vg">

	<!-- open graph metadata-->
	<meta property="og:title" content="AXR: The web, done right #axr" />
	<meta property="og:type" content="Open Source Technology" />
	<meta property="og:url" content="http://axr.vg" />
	<meta property="og:image" content="http://axr.vg/resources/images/logo_small.jpg" />
	<meta property="og:description" content="AXR is a new standard for making web sites and apps, replacing HTML with XML and CSS with HSS. A unified platform independent of browsers, possible to install as plug-in." />
	<meta property="og:site_name" content="AXR: Arbitrary XML Rendering" />
	<!-- google +snippet metadata-->
	<link href="https://plus.google.com/105865857923622443169" rel="publisher" />
	<meta itemprop="name" content="AXR: The web, done right #axr">
	<meta itemprop="description" content="AXR is a new standard for making web sites and apps, replacing HTML with XML and CSS with HSS. A unified platform independent of browsers, possible to install as plug-in.">
	<meta itemprop="image" content="http://axr.vg/resources/images/logo_small.jpg">
	<!-- unleash the beast-->
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<?php print $styles; ?>
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body class="<?php print $classes; ?>" <?php print $attributes;?>>
	<?php print $page_top; ?>
	<?php print $page; ?>
	<?php print $page_bottom; ?>
	<?php print $scripts; ?>

	<script>
		var _gaq=[['_setAccount','UA-20384487-1'],['_trackPageview']]; // Change UA-XXXXX-X to be your site's ID
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
		g.src=('https:'==location.protocol?'https://ssl':'https://www')+'.google-analytics.com/ga.js';
		s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>

	<!--[if lt IE 7 ]>
		<script src="https://ajax.googleapis.com/ajax/libs/chrome-frame/1.0.2/CFInstall.min.js"></script>
		<script>window.attachEvent("onload",function(){CFInstall.check({mode:"overlay"})})</script>
	<![endif]-->
</body>
</html>
