<?php

$page_title = 'Videos';

$page_header = 'Videos';

script([
	'/scripts/external/script-inputmask.js',
	'/scripts/script-pagination.js',
	'/videos/script-index.js',
]);

style([
	'/style/style-pagination.css',
]);

?>

<div class="col c4-ABBB">
	
	<form action="/videos/" class="videos__sidebar" enctype="multipart/form-data" method="get" name="filter_videos">
		
		<ul class="text">
			
			<li class="input__row">
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Sort by</label>
					
					<select class="input input__select" name="sort">
						<?php
							foreach([
								'date_occurred' => 'Date uploaded',
								'date_added' => 'Date added',
								'num_views' => 'Most views'
							] as $key => $string) {
								echo '<option value="'.$key.'" '.($key == $_GET['order'] ? 'checked' : null).' >'.$string.'</option>';
							}
						?>
					</select>
					
				</div>
			</li>
			
			<li class="input__row">
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Video types</label>
					
					<?php foreach($access_video->video_types as $type_name => $type_key): ?>
						<label class="input__checkbox">
							<input class="input__choice" name="type_<?= $type_key; ?>" type="checkbox" value="<?= $type_name; ?>" <?= $_GET['type_'.$type_key] == $type_name ? 'checked' : null; ?> />
							<span class="symbol__unchecked"><?= strlen($type_name) < 3 ? strtoupper($type_name) : $type_name; ?></span>
						</label>
					<?php endforeach; ?>
					
				</div>
			</li>
			
			<li class="input__row">
				<div class="input__group">
					
					<label class="input__label">Date published</label>
					<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_occurred" placeholder="yyyy-mm-dd" size="10" value="<?= sanitize($_GET['date_occurred']); ?>" />
					
				</div>
			</li>
			
			<li class="input__row">
				<div class="input__group any--flex-grow">
					
					<button class="any--flex-grow" name="submit" type="submit">
						Filter
					</button>
					
				</div>
			</li>
			
		</ul>
		
	</form>
	
	<div class="videos__wrapper pagination__wrapper">
		<?php include('../videos/partial-index.php'); ?>
	</div>
	
</div>