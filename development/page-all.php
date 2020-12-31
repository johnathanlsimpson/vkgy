<?php

style([
	'/development/style-page-all.css',
]);

$page_title = 'All development updates';

?>

<div class="col c1">
	
	<h2>
		<?= lang('Past updates', '過去の情報', 'div'); ?>
	</h2>
	
	<div class="past__container any--flex">
		<?php foreach($entries as $year => $months): ?>
			<?php foreach($months as $month => $days): ?>
				
				<div class="past__month text text--outlined">
					
					<h3><?= date('F', strtotime('2000-'.$month)).' '.$year; ?></h3>
					
					<div class="past__days">
						<?php foreach($days as $entry): ?>
							
							<a class="past__day" href="<?= '/development/'.$entry['id'].'/'; ?>">
								<?= date( 'jS', strtotime($entry['date_occurred']) ); ?>
							</a>
							
						<?php endforeach; ?>
					</div>
					
				</div>
				
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>
	
</div>