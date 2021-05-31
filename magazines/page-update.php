<?php

// ========================================================
// Inclusions
// ========================================================

include_once('../php/function-render_json_list.php');
render_json_list('label');

// ========================================================
// States
// ========================================================

// Set flag
$is_edit = $magazine ? true : false;

// We'll use these strings a lot
$add_text_en = 'Add magazine';
$add_text_ja = '叢書を追加';
$edit_text_en = 'Edit magazine';
$edit_text_ja = '叢書を編集する';

// Do some weird stuff with the subnav to make sure it shows appropriate text
$text_en    = $is_edit ? $edit_text_en : $add_text_en;
$text_ja    = $is_edit ? $edit_text_ja : $add_text_ja;
$subnav_url = $is_edit ? $magazine['url'].'edit/' : '/magazines/add/';

// ========================================================
// Page setup
// ========================================================

script([
	'/scripts/external/script-alpine.js',
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/magazines/script-page-update.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
]);

subnav([
	lang($text_en, $text_ja, [ 'primary_attributes' => 'data-add-text="'.$add_text_en.'" data-edit-text="'.$edit_text_en.'"', 'secondary_attributes' => 'data-add-text="'.$add_text_ja.'" data-edit-text="'.$edit_text_ja.'"', 'container' => 'span', 'secondary_class' => 'any--hidden' ]) => $subnav_url,
]);

$page_header = lang('Magazines', '叢書', 'div');

$page_title = $is_edit ? $edit_text_en.' ('.$edit_text_ja.')' : $add_text_en.' ('.$add_text_ja.')';

// ========================================================
// Get additional data
// ========================================================
	
// Get text before/after num
$volume_pattern = explode('{volume}', $magazine['volume_name_pattern']);
$before_num = $volume_pattern[0];
$after_num = $volume_pattern[1] ?: null;

// Get romaji before/after num
$volume_romaji_pattern = explode('{volume}', $magazine['volume_romaji_pattern']);
$before_num_romaji = $volume_romaji_pattern[0] ?: null;
$after_num_romaji = $volume_romaji_pattern[1] ?: null;

// Get extant magazine magazine
$magazines = $access_magazine->access_magazine([ 'get' => 'basics' ]);

?>

<form action="/magazines/function-update.php" class="col c1" enctype="multipart/form-data" method="post" name="update-magazine">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<input name="id" data-get="id" data-get-into="value" placeholder="id" type="hidden" value="<?= $magazine['id']; ?>" />
	
	<h2 data-add-text="<?= sanitize( lang($add_text_en, $add_text_ja, 'div') ); ?>" data-edit-text="<?= sanitize( lang($edit_text_en, $edit_text_ja, 'div') ); ?>">
		<?= $is_edit ? lang($edit_text_en, $edit_text_ja, 'div') : lang($add_text_en, $add_text_ja, 'div'); ?>
	</h2>
	
	<?php
		
		// If adding new magazine, loop a few times; otherwise just show magazine in question
		$num_loops = $is_edit ? 1 : 4;
		
		for($i=0; $i<$num_loops; $i++) {
			?>
				<ul class="text" x-data="{
					showPattern:false,
					beforeNumber:'<?= $before_num ?: 'Vol.'; ?>',
					beforeNumberRomaji:'<?= $before_num_romaji; ?>',
					numDigits:<?= strlen($magazine['num_volume_digits']) ? $magazine['num_volume_digits'] : 2; ?>,
					afterNumber:'<?= $after_num; ?>',
					afterNumberRomaji:'<?= $after_num_romaji; ?>'
				}">
					
					<!-- Name and parent -->
					<li class="input__row">
						
						<!-- Name -->
						<div class="input__group any--flex-grow">
							<label class="input__label"><?= lang('Magazine name', '叢書の名', 'hidden'); ?></label>
							<input class="input" name="name[]" placeholder="magazine name" value="<?= $magazine['name']; ?>" />
							<input class="input--secondary" name="romaji[]" placeholder="(romaji)" value="<?= $magazine['romaji']; ?>" />
						</div>
						
						<!-- Parent magazine -->
						<div class="input__group">
							<label class="input__label"><?= lang('Parent magazine', '親シリーズ', 'hidden'); ?></label>
							<select class="input" name="parent_magazine_id[]" placeholder="parent">
								<option value="" selected></option>
								<?php foreach($magazines as $temp_magazine): ?>
									<option value="<?= $temp_magazine['id']; ?>" <?= $magazine['parent_magazine_id'] == $temp_magazine['id'] ? 'selected' : null; ?> ><?= $temp_magazine['romaji'] ? $temp_magazine['romaji'].' ('.$temp_magazine['name'].')' : $temp_magazine['name']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						
					</li>
					
					<!-- Volume Pattern -->
					<li class="input__row">
						<div class="input__group">
							<label class="input__label"><?= lang('Volume name', '号パターン', 'hidden'); ?></label>
							
							<span class="any--weaken-color" style="line-height:2rem;">Pattern:&nbsp;</span>
							
							<span class="any__note" style="display:inline-flex;white-space:pre;margin-bottom:0.25rem;">
								
								<span x-text="beforeNumberRomaji ? beforeNumberRomaji : beforeNumber"></span>
								<span x-text="numDigits > 0 ? '0'.repeat(numDigits - 1) + '1' : 'XXX'"></span>
								<span x-text="afterNumberRomaji ? afterNumberRomaji : afterNumber"></span>
								
								<span x-text="beforeNumberRomaji || afterNumberRomaji ? '&nbsp;(' + beforeNumber : ''"></span>
								<span x-text="beforeNumberRomaji || afterNumberRomaji ? ( numDigits > 0 ? '0'.repeat(numDigits - 1) + '1' : 'XXX' ) : ''"></span>
								<span x-text="beforeNumberRomaji || afterNumberRomaji ? afterNumber + ')' : ''"></span>
								
							</span>
							
							&nbsp;
							
							<a class="symbol__edit" href="#" style="line-height:2rem;" x-on:click="showPattern=true" x-show="!showPattern">edit</a>
							
						</div>
						
						<!-- Before number -->
						<div class="input__group" x-show="showPattern">
							
							<label class="input__label"><?= lang('Before', '号の前', 'hidden'); ?></label>
							<input name="before_number[]" placeholder="text before number" value="<?= $before_num ?: 'Vol.'; ?>" x-model="beforeNumber" />
							<input class="input--secondary" name="before_number_romaji[]" placeholder="(romaji)" value="<?= $before_num_romaji ?: null; ?>" x-model="beforeNumberRomaji" />
							
						</div>
						
						<!-- Number style -->
						<div class="input__group" x-show="showPattern">
							
							<label class="input__label"><?= lang('Num. digits', '桁数', 'hidden'); ?></label>
							<select class="input" name="num_volume_digits[]" x-model="numDigits">
								<option value="0" <?= $magazine['num_volume_digits'] == 0 ? 'selected' : null; ?> >(allow text)</option>
								<option value="1" <?= $magazine['num_volume_digits'] == 1 ? 'selected' : null; ?> >1</option>
								<option value="2" <?= $magazine['num_volume_digits'] == 2 || !strlen($magazine['num_volume_digits']) ? 'selected' : null; ?> >2</option>
								<option value="3" <?= $magazine['num_volume_digits'] == 3 ? 'selected' : null; ?> >3</option>
								<option value="4" <?= $magazine['num_volume_digits'] == 4 ? 'selected' : null; ?> >4</option>
							</select>
							
						</div>
						
						<!-- After number -->
						<div class="input__group" x-show="showPattern">
							
							<label class="input__label"><?= lang('After', '号の後', 'hidden'); ?></label>
							<input name="after_number[]" placeholder="text after number" x-model="afterNumber" value="<?= $after_num; ?>" />
							<input class="input--secondary" name="after_number_romaji[]" placeholder="(romaji)" value="<?= $after_num_romaji; ?>" x-model="afterNumberRomaji" />
							
						</div>
						
					</li>
					
					<!-- Defaults -->
					<li class="input__row">
						
						<!-- Default price -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Default price', '', 'hidden'); ?></label>
							<input name="default_price[]" placeholder="e.g. 800 yen" value="<?= $magazine['default_price']; ?>" />
							
						</div>
						
						<!-- Labels -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Publisher', '発行者', 'hidden'); ?></label>
							<select class="input" data-multiple data-source="labels" name="labels[<?= $i; ?>][]" placeholder="publishers" multiple data-multiple="true">
								<option></option>
								<?php
									if( is_array($magazine['labels']) && !empty($magazine['labels']) ) {
										foreach( $magazine['labels'] as $label ) {
											echo '<option value="'.$label['id'].'" selected></option>';
										}
									}
								?>
							</select>
							
						</div>
						
					</li>
					
				</ul>
			<?php
		}
	?>
	
	<div class="text text--docked">
		<div class="input__row">
			<div class="input__group any--flex-grow">
				<button class="any--flex-grow" name="submit" type="submit">
					<?= lang('Add magazine', '叢書を追加', 'hidden'); ?>
				</button>
				<span data-role="status"></span>
			</div>
		</div>
		<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
	</div>
	
</form>