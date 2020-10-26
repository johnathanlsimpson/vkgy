<?php

$page_title = 'Videos';

$page_header = 'Videos';

script([
	'/scripts/external/script-inputmask.js',
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-pagination.js',
	'/videos/script-index.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/style/style-pagination.css',
]);

?>
	
<input class="moderation__choice" id="show_moderation" type="checkbox" hidden  />

<div class="col c4-ABBB videos__row">
	
	<form action="/videos/" class="videos__sidebar" enctype="multipart/form-data" method="get" name="filter_videos">
		
		<h3>
			Filters
		</h3>
		
		<ul class="text">
			
			<li class="input__row">
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Sort by</label>
					
					<select class="input" name="sort">
						<?php
							foreach([
								'date_occurred' => 'date uploaded',
								'date_added' => 'date added',
								'num_views' => 'most views'
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
							<input class="input__choice" name="type_<?= $type_key; ?>" type="checkbox" value="<?= $type_name; ?>" <?= !is_array($type) || in_array($type_key, $type) ? 'checked' : null; ?> />
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
			
			<?php if( $_SESSION['can_approve_data'] ): ?>
			<!-- Start moderation -->
			
			<li class="input__row moderation--show">
				<div class="input__group">
					
					<label class="input__label">Flagged videos</label>
					
					<?php foreach([ -1 => 'all', 1 => 'flagged', 0 => 'approved' ] as $flagged_key => $flagged_name): ?>
						<label class="input__radio">
							<input class="input__choice" name="is_flagged" type="radio" value="<?= $flagged_key >= 0 ? $flagged_key : null; ?>" <?= $flagged_key == $is_flagged ? 'checked' : null; ?> />
							<span class="symbol__unchecked"><?= $flagged_name; ?></span>
						</label>
					<?php endforeach; ?>
					
				</div>
			</li>
			
			<li class="input__row moderation--show">
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Added by user</label>
					
					<select class="input any--flex-grow" name="user_id" placeholder="user">
						<option value="" <?= !is_numeric($user_id) ? 'selected' : null; ?>>(any user)</option>
						<?php foreach($users as $user): ?>
							<option value="<?= $user['id']; ?>" <?= $user['id'] === $user_id ? 'selected' : null; ?>><?= $user['username']; ?></option>
						<?php endforeach; ?>
					</select>
					
				</div>
			</li>
			
			<!-- End moderation -->
			<?php endif; ?>
			
			<li class="input__row">
				
				<div class="input__group any--flex-grow">
					<button class="any--flex-grow" name="submit" type="submit">Filter</button>
				</div>
				
				<div class="input__group">
					<button name="clear">Reset</button>
				</div>
				
			</li>
			
		</ul>
		
		<?php if($_SESSION['can_approve_data']): ?>
			<ul class="text text--outlined moderation--hide">
				<li class="input__row">
					<div class="input__group any--flex-grow">
						
						<label class="input__button any--flex-grow" for="show_moderation" style="text-align:center;">Moderation tools</label>
						
					</div>
				</li>
			</ul>
		<?php endif; ?>
		
	</form>
	
	<div class="videos__wrapper pagination__wrapper">
		<?php include('../videos/partial-index.php'); ?>
	</div>
	
</div>

<style>
	.moderation__choice:checked + .videos__row .moderation--hide {
		display: none;
	}
	.moderation__choice:not(:checked) + .videos__row .moderation--show {
		display: none;
	}
</style>