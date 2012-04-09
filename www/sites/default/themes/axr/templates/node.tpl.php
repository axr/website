<div id="node-<?php echo $node->nid; ?>" class="<?php echo $classes; ?>"<?php echo $attributes; ?>>
	<h1><?php echo $node->title; ?></h1>

	<?php /*if ($display_submitted): ?>
		<span class="submitted"><?php echo $submitted ?></span>
	<?php endif;*/ ?>

	<div class="content clearfix"<?php echo $content_attributes; ?>>
		<?php
			hide($content['comments']);
			hide($content['links']);

			echo render($content);
		?>
	</div>

	<div class="clearfix">
		<?php if (!empty($content['links'])): ?>
			<div class="links"><?php echo render($content['links']); ?></div>
		<?php endif; ?>

		<?php echo render($content['comments']); ?>
	</div>
</div>

