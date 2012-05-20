<div id="comments" class="<?php echo $classes; ?>"<?php echo $attributes; ?>>
	<?php if ($content['comments'] && $node->type != 'forum'): ?>
		<h2 class="title"><?php echo t('Comments'); ?></h2>
	<?php endif; ?>

	<?php echo render($content['comments']); ?>

	<?php if ($content['comment_form']): ?>
		<h2 class="title comment-form"><?php echo t('Add new comment'); ?></h2>
		<?php echo render($content['comment_form']); ?>
	<?php else: ?>
		<div class="links">
			<ul class="links inline">
				<li class="last">
					<span>
						<a href="/user/login?destination=<?php echo urlencode(request_uri()); ?>%23comment-form">Log in</a> or
						<a href="/user/register?destination=<?php echo urlencode(request_uri()); ?>%23comment-form">register</a> to post comments
					</span>
				</li>
			</ul>
		</div>
	<?php endif; ?>
</div>

