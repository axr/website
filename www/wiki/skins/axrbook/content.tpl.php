<div id="globalWrapper">
	<div id="column-content">
		<?php $this->cactions(); ?>
		<div id="content">
			<a id="top"></a>
			<?php if ($this->data['sitenotice']): ?>
				<div id="siteNotice"><?php $this->html('sitenotice') ?></div>
			<?php endif; ?>

			<h1 id="firstHeading" class="firstHeading"><?php $this->html('title') ?></h1>

			<div id="bodyContent">
				<div id="siteSub"><?php $this->msg('tagline') ?></div>
				<div id="contentSub"<?php $this->html('userlangattributes') ?>><?php $this->html('subtitle') ?></div>
				<?php if($this->data['undelete']) { ?>
					<div id="contentSub2"><?php $this->html('undelete') ?></div>
				<?php } ?>
				<?php if($this->data['newtalk'] ) { ?>
					<div class="usermessage"><?php $this->html('newtalk')  ?></div>
				<?php } ?>
				<?php if($this->data['showjumplinks']) { ?>
					<div id="jump-to-nav">
						<?php $this->msg('jumpto') ?>
						<a href="#column-one"><?php $this->msg('jumptonavigation') ?></a>,
						<a href="#searchInput"><?php $this->msg('jumptosearch') ?></a>
					</div>
				<?php } ?>

				<?php $this->html('bodytext') ?>
				<?php if($this->data['catlinks']) { $this->html('catlinks'); } ?>
				<?php if($this->data['dataAfterContent']) { $this->html ('dataAfterContent'); } ?>
				<div class="visualClear"></div>
			</div>
		</div>

		<div id="footer">
			<?php
				$validFooterLinks = $this->getFooterLinks("flat");
				$infoLinks = array('lastmod', 'viewcount');
			?>
			<?php if (count($validFooterLinks) > 0): ?>
				<div class="links">
					<div class="info">
						<?php
							foreach ($validFooterLinks as $link) {
								if (in_array($link, $infoLinks)) {
									$this->html($link);
									echo ' ';
								}
							}
						?>
					</div>

					<?php
						foreach ($validFooterLinks as $link) {
							if (!in_array($link, $infoLinks)) {
								$this->html($link);
							}
						}
					?>
				</div>
			<?php endif; ?>

			<img src="http://axrvg.sygise.aragnis.com/wiki/skins/common/images/poweredby_mediawiki_88x31.png" class="poweredby" />
			<br style="clear: both" />
		</div>
	</div>
	<div id="column-one"<?php $this->html('userlangattributes'); ?>>
		<div class="portlet" id="p-personal">
			<h5><?php $this->msg('personaltools') ?></h5>
			<div class="pBody">
				<ul<?php $this->html('userlangattributes') ?>>
					<?php $pt = $this->getPersonalTools(); ?>
					<?php foreach ($pt as $key => $item): ?>
						<?php echo $this->makeListItem($key, $item); ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>

		<?php $this->renderPortals($this->data['sidebar']); ?>
	</div>
	<div class="visualClear"></div>
</div>

