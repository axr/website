<div class="<?php echo $classes; ?> clearfix"<?php echo $attributes; ?>>
	<?php echo $picture; ?>

	<div class="nested_0">
		<?php echo render($content['links']); ?>

		<?php /*if ($new): ?>
			<span class="new"><?php echo $new ?></span>
		<?php endif;*/ ?>

		<h3<?php echo $title_attributes; ?>><?php echo $title; ?></h3>

		<div class="submitted">
			<?php echo $submitted; ?>
		</div>

		<div class="content"<?php echo $content_attributes; ?>>
			<?php
				hide($content['links']); // We'll render them later
				echo render($content);
			?>
			<?php if ($signature): ?>
				<div class="user-signature">
					<?php echo $signature; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

