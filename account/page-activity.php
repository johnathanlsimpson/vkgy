<div class="col c1">
	<div>
		<?php include('partial-card.php'); ?>
	</div>
</div>

<div class="col c1">
	<div>
		<h2>
			<?= lang( $user['username'].'\'s activity', $user['username'].'の活動', 'div' ); ?>
		</h2>
		<ul class="text">
			<?php
				$activity_limit = 100;
				$activity_offset = (is_numeric($_GET['page']) ? $_GET['page'] : 0) * $activity_limit;
				include('partial-activity.php');
			?>
		</ul>
	</div>
</div>