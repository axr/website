<div id="downloads">
	<h2>Prototype</h2>

	<?php
		$releases = axrreleases_get_releases(0, 5);
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
								<?php if (count((array) $release->urls->$os) == 0): ?><span>not available</span><?php else: ?>
									<a href="#" class="dlink" data-version="<?php echo $release->version; ?>" data-os="<?php echo $os; ?>"><span class="block_0 <?php echo $os; ?>"></span>download</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<div class="none">No releases are currently available</div>
	<?php endif; ?>

	<script>
		window.axr_downloads = <?php
			$data = array();

			if (count($releases) > 0)
			{
				foreach ($releases as $release)
				{
					$data[$release->version] = $release->urls;
				}
			}

			echo json_encode($data);
		?>;
	</script>
</div>

<div id="downloads__ask_arch">
	<div class="outer">
		<div class="inner">
			<a href="#" class="close">☓</a>
			<h2>You're downloading AXR prototype</h2>
			<p>You're about to download AXR prototype version <span class="version">unknown</span> for <span class="os">unknown</span>. Please select your system architecture from below:</p>
			<div class="options"></div>
		</div>
	</div>
</div>

