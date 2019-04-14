<div class="col c3 any--weaken-color any--margin">
	<div>
		<?php
			if($page > 1) {
				?>
					<a class="symbol__previous" href="/comments/&page=<?php echo ($page - 1); ?>">Page <?php echo ($page - 1); ?></a>
				<?php
			}
			else {
				echo 'Page 1';
			}
		?>
	</div>
	<div style="text-align: center;">
		Results <?php echo ($offset + 1).' to '.($offset + $num_comments); ?>
	</div>
	<div style="text-align: right;">
		<?php
			if($num_total_pages > $page) {
				?>
					<a class="symbol__next" href="/comments/&page=<?php echo ($page + 1); ?>">Page <?php echo ($page + 1); ?></a>
				<?php
			}
			else {
				echo 'Page '.($page ?: 1);
			}
		?>
	</div>
</div>