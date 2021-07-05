<?php

// ========================================================
// Inclusions
// ========================================================

// Get list of artists--song lists are added in JS, depending on selected artist
include_once('../php/function-render_json_list.php');
render_json_list('artist');

// ========================================================
// Page setup
// ========================================================

script([
	'/scripts/external/script-alpine.js',
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-getJsonLists.js',
	'/songs/script-page-add.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/songs/style-page-add.css',
]);

$page_title = 'Add songs (曲を追加)';

$page_header = lang('Add songs', '曲を追加', 'div');

?>

<form action="/songs/function-add.php" class="col c1" enctype="multipart/form-data" method="post" name="add-song">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<h2>
		<?= lang('Add songs', '曲を追加', 'div'); ?>
	</h2>
	
	<!-- Artist -->
	<div class="text">
		<div class="input__row">
			<div class="input__group any--flex-grow">
				
				<label class="input__label"><?= lang('Artist', 'アーティスト', 'hidden'); ?></label>
				
				<select class="input any--flex-grow" data-source="artists" name="artist_id" placeholder="select artist">
					<option></option>
					<?= $artist ? '<option value="'.$artist['id'].'" selected>'.($artist['romaji'] ?: $artist['name']).'</option>' : null; ?>
				</select>
				
			</div>
		</div>
	</div>
	
	<!-- Songs -->
	<ul class="text">
		<?php for($i=0;$i<5;$i++): ?>
			<li class="input__row" x-data="{ showHint:0 }">
				
				<!-- Name -->
				<div class="input__group any--flex-grow">
					
					<label class="input__label"><?= lang('Song name', '曲の名', 'hidden'); ?></label>
					<input name="name[]" data-clear-on-success placeholder="song name" value="" />
					<input class="input--secondary" data-clear-on-success name="romaji[]" placeholder="(romaji)" value="" />
					
				</div>
				
				<!-- Hint -->
				<div class="input__group" x-show="!showHint">
					<a class="symbol__plus" href="#" style="line-height:2rem;" @click.prevent="showHint=1">hint</a>
				</div>
				
				<!-- Hint -->
				<div class="input__group any--flex-grow" x-show="showHint">
					<label class="input__label"><?= lang('Hint', 'ヒント', 'hidden'); ?></label>
					<input class="any--flex-grow" data-clear-on-success name="hint[]" placeholder="e.g. rerecording" value="" />
				</div>
				
			</li>
		<?php endfor; ?>
	</ul>
	
	<!-- Submit -->
	<div class="text text--docked">
		<div class="input__row">
			<div class="input__group any--flex-grow">
				<button class="any--flex-grow" name="submit" type="submit">
					<?= lang('Add songs', '曲を追加', 'hidden'); ?>
				</button>
				<span data-role="status"></span>
			</div>
		</div>
		<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
	</div>
	
</form>