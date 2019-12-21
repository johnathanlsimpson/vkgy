<?php

if($error) {
	?>
		<div class="col c1">
			<div class="text text--outlined text--error symbol__error">
				<?= $error; ?>
			</div>
		</div>
	<?php
}

print_r($edit);

?>
	<div class="col c1">
		<h2>
			Edit history
		</h2>
		<ul class="text">
			<?php
				if(is_array($edits) && !empty($edits)) {
					foreach($edits as $edit) {
						?>
							<li>
								<?= substr($edit['date_occurred'], 0, 10); ?>
								<span class="any--weaken-color"><?= substr($edit['date_occurred'], 11); ?></span>
								
								<a class="user" href="<?= '/users/'.$edit['username'].'/'; ?>"><?= $edit['username']; ?></a>
								
								<?= print_r($edit['content'], true); ?>
								
								<a href="<?= '/artists/'.$artist['friendly'].'/edits/'.$edit['id'].'/'; ?>">
									View details <span class="symbol__next"></span>
								</a>
							</li>
						<?php
					}
				}
			?>
		</ul>
	</div>
<?php