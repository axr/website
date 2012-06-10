<?php ob_start(); ?>
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
	<?php echo isset($messages) ? $messages : ''; ?>

	<?php /*if (!$is_front): ?>
		<?php print render($tabs); ?>
	<?php endif;*/ ?>

	<?php print render($page['content']); ?>
<?php endif; ?>
<?php

$page = ob_get_contents();
ob_end_clean();

$view = axr_get_view();
$view->_content = $page;

if (!$is_front)
{
	// Yes, it's a really dirty way to do this, but that's what Drupal is
	$view->_breadcrumb = $breadcrumb;
}

