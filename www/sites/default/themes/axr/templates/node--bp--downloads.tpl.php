<div class="downloads">
	<h2>Prototype</h2>

	<?php
		$releases = axr_get_releases(0, 5);
		$arches = array(
			'x86-64' => '64-bit (x86-64)',
			'x86' => '32-bit (x86)'
		);
	?>
	<?php if (count($releases) > 0): ?>
		<ul class="dtable">
			<li class="head">
				<span class="version">Version</span>
				<ul>
					<li>Windows</li>
					<li>OS X</li>
					<li>Linux</li>
				</ul>
			</li>
			<?php foreach ($releases as $release): ?>
				<li>
					<span class="version"><?php echo $release->version; ?></span>
					<ul>
						<?php foreach (array('win', 'osx', 'linux') as $os): ?>
							<li>
								<a href="#"><span class="block_0 <?php echo $os; ?>"></span>download</a>
								<ul class="archmenu">
									<?php $count = 0; ?>
									<?php foreach ($release->urls->$os as $arch => $url): ?>
										<?php $count++; ?>
										<li><a href="<?php echo $url; ?>"><?php echo isset($arches[$arch]) ? $arches[$arch] : $arch; ?></a></li>
									<?php endforeach; ?>
									<?php if ($count === 0): ?>
										<li class="na">Not available</li>
									<?php endif; ?>
								</ul>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<div class="none">No releases are currently available</div>
	<?php endif; ?>
</div>

