<?php

	$page_header = lang(
		$user['username'].'\''.(substr($user['username'], -1) == 's' ? null : 's').' activity',
		$user['username'].'の活動',
		'div'
	);
	
	include('head-user.php');
?>

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