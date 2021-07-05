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
	'/scripts/external/script-inputmask.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initDelete.js',
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

// Previous magazine
$sql_prev = 'SELECT * FROM magazines WHERE friendly<? ORDER BY friendly DESC LIMIT 1';
$stmt_prev = $pdo->prepare($sql_prev);
$stmt_prev->execute([ $magazine['friendly'] ]);
$rslt_prev = $stmt_prev->fetch();

if( $rslt_prev ) {
	subnav([
		[
			'text' => $rslt_prev['romaji'] ? lang( $rslt_prev['romaji'], $rslt_prev['name'], 'hidden' ) : $rslt_prev['name'],
			'url' => '/magazines/'.$rslt_prev['friendly'].'/edit/',
			'position' => 'left',
		],
	], 'directional');
}

// Next magazine
$sql_next = 'SELECT * FROM magazines WHERE friendly>? ORDER BY friendly ASC LIMIT 1';
$stmt_next = $pdo->prepare($sql_next);
$stmt_next->execute([ $magazine['friendly'] ]);
$rslt_next = $stmt_next->fetch();

if( $rslt_next ) {
	subnav([
		[
			'text' => $rslt_next['romaji'] ? lang( $rslt_next['romaji'], $rslt_next['name'], 'hidden' ) : $rslt_next['name'],
			'url' => '/magazines/'.$rslt_next['friendly'].'/edit/',
			'position' => 'right',
		],
	], 'directional');
}
	
// Get text before/after num
$volume_pattern = explode('{volume}', $magazine['volume_name_pattern']);
$before_num = $volume_pattern[0];
$after_num = $volume_pattern[1] ?: null;

// Get romaji before/after num
$volume_romaji_pattern = explode('{volume}', $magazine['volume_romaji_pattern']);
$before_num_romaji = $volume_romaji_pattern[0] ?: null;
$after_num_romaji = $volume_romaji_pattern[1] ?: null;

// Get other magazines
$magazines = $access_magazine->access_magazine([ 'get' => 'basics' ]);

// ========================================================
// Transform data
// ========================================================

// Add empty row to $magazine_attributes just to make it easier to display a sort of “edit + add one more” section
$magazine_attributes[-1] = [ [] ];

?>

<form action="/magazines/function-update.php" class="col c1" enctype="multipart/form-data" method="post" name="update-magazine">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<h2 data-add-text="<?= sanitize( lang($add_text_en, $add_text_ja, 'div') ); ?>" data-edit-text="<?= sanitize( lang($edit_text_en, $edit_text_ja, 'div') ); ?>">
		<?= $is_edit ? lang($edit_text_en, $edit_text_ja, 'div') : lang($add_text_en, $add_text_ja, 'div'); ?>
	</h2>
	
	<?php
		
		// If adding new magazine, loop a few times; otherwise just show magazine in question
		$num_loops = 1;
		
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
						
						<!-- ID -->
						<input data-get="id" data-get-into="value" name="id[]" placeholder="id" type="hidden" value="<?= $magazine['id']; ?>" />
						<?php if( $is_edit ): ?>
							<div class="input__group any--weaken-color">
								<label class="input__label">ID</label>
								<span class="any__note" style="line-height:2rem;padding:0 0.5rem;"><?= $magazine['id']; ?></span>
							</div>
						<?php endif; ?>
						
						<!-- Name -->
						<div class="input__group any--flex-grow">
							<label class="input__label"><?= lang('Magazine name', '叢書の名', 'hidden'); ?></label>
							<input class="input" name="name[]" placeholder="magazine name" value="<?= $magazine['name']; ?>" />
							<input class="input--secondary" name="romaji[]" placeholder="(romaji)" value="<?= $magazine['romaji']; ?>" />
						</div>
						
						<!-- Friendly -->
						<?php if( $is_edit ): ?>
						<div class="input__group">
							<label class="input__label"><?= lang('Friendly', 'スラッグ', 'hidden'); ?></label>
							<input class="input" name="friendly[]" placeholder="friendly" value="<?= $magazine['friendly']; ?>" />
						</div>
						<?php endif; ?>
						
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
						
						<!-- Format -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Format', '形式', 'hidden'); ?></label>
							<select class="input" name="type[<?= $i; ?>]" placeholder="format">
								<?php
									if( is_array($magazine_attributes[0]) && !empty($magazine_attributes[0]) ) {
										foreach( $magazine_attributes[0] as $type ) {
											echo '<option value="'.$type['id'].'" '.( $type['id'] == $magazine['type'] || ( !$magazine['type'] && $type['is_default'] ) ? 'selected' : null ).'>';
											echo $type['romaji'] ? $type['romaji'].' ('.$type['name'].')' : $type['name'];
											echo '</option>';
										}
									}
								?>
							</select>
							
						</div>
						
						<!-- Size -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Size', '判型', 'hidden'); ?></label>
							<select class="input" name="size[<?= $i; ?>]" placeholder="size">
								<option></option>
								<?php
									if( is_array($magazine_attributes[1]) && !empty($magazine_attributes[1]) ) {
										foreach( $magazine_attributes[1] as $size ) {
											echo '<option value="'.$size['id'].'" '.( $size['id'] == $magazine['size'] ? 'selected' : null ).'>';
											echo $size['romaji'] ? $size['romaji'].' ('.$size['name'].')' : $size['name'];
											echo '</option>';
										}
									}
								?>
							</select>
							
						</div>
						
						<!-- Default price -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Default price', '価格', 'hidden'); ?></label>
							<input name="default_price[]" placeholder="e.g. 800 yen" size="12" value="<?= $magazine['default_price']; ?>" />
							
						</div>
						
						<!-- Labels -->
						<div class="input__group any--flex-grow">
							
							<label class="input__label"><?= lang('Publisher', '発行者', 'hidden'); ?></label>
							<select class="input any--flex-grow" data-multiple data-source="labels" name="labels[<?= $i; ?>][]" placeholder="publishers" multiple data-multiple="true">
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
					
					<!-- Dates -->
					<li class="input__row">
						
						<!-- Start date -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Start date', '開始日', 'hidden'); ?></label>
							<input class="input" data-inputmask="'alias': '9999[-99][-99]'" maxlength="10" name="date_occurred[]" placeholder="yyyy-mm-dd" size="10" value="<?= $magazine['date_occurred']; ?>" />
							
						</div>
						
						<!-- Frequency -->
						<div class="input__group">
							
							<label class="input__label"><?= lang('Frequency', '発行', 'hidden'); ?></label>
							<select class="input" name="frequency[]" placeholder="frequency">
								<option value="" selected></option>
								<?php
									if( is_array($magazine_attributes[3]) && !empty($magazine_attributes[3]) ) {
										foreach( $magazine_attributes[3] as $frequency ) {
											echo '<option value="'.$frequency['id'].'" '.( $frequency['id'] == $magazine['frequency'] || ( !strlen($magazine['frequency']) && $frequency['is_default'] ) ? 'selected' : null ).'>';
											echo $frequency['romaji'] ? $frequency['romaji'].' ('.$frequency['name'].')' : $frequency['name'];
											echo '</option>';
										}
									}
								?>
							</select>
							
						</div>
						
					</li>
					
				</ul>
				
				<!-- Delete -->
				<?php if( $is_edit && $_SESSION['can_delete_data'] ): ?>
					<div class="text text--outlined">
						<div class="input__row">
							
							<div class="input__group">
								<label class="input__label"><?= lang('Delete', '消す', 'hidden'); ?></label>
								<button class="symbol__delete symbol--standalone" name="delete" type="button"></button>
							</div>
							
						</div>
					</div>
				<?php endif; ?>
				
				<!-- Attributes -->
				<?php if( !$is_edit && $_SESSION['is_boss'] ): ?>
					<h3>
						<?= lang('Edit attributes', '属性を編集する', 'div'); ?>
					</h3>
					
					<div class="text text--outlined">
						
						<?php foreach( $magazine_attributes as $group_key => $attribute_group ): ?>
							<ul>
								
								<h4 style="margin-bottom:0.5rem;">
									<?= magazine::$attribute_types[ $group_key ] ?: 'add new attribute'; ?>
								</h4>
								
								<?php foreach( $attribute_group as $attribute ): ?>
									<li class="input__row">
										
										<!-- ID -->
										<input name="attributes[id][]" value="<?= $attribute['id']; ?>" type="hidden" />
										
										<!-- Name -->
										<div class="input__group any--flex-grow">
											
											<label class="input__label"><?= lang('Name', '叢書の名', 'hidden'); ?></label>
											<input name="attributes[name][]" placeholder="name" value="<?= $attribute['name']; ?>" />
											<input class="input--secondary" name="attributes[romaji][]" placeholder="(romaji)" value="<?= $attribute['romaji']; ?>" />
											
										</div>
										
										<!-- Friendly -->
										<div class="input__group">
											
											<label class="input__label"><?= lang('Friendly', 'スラッグ', 'hidden'); ?></label>
											<input name="attributes[friendly][]" placeholder="friendly" value="<?= $attribute['friendly']; ?>" />
											
										</div>
										
										<!-- Type -->
										<div class="input__group">
											
											<label class="input__label"><?= lang('Type', '種類', 'hidden'); ?></label>
											<select class="input" name="attributes[type][]" placeholder="type">
												<?php foreach( magazine::$attribute_types as $type_key => $type_name ): ?>
													<option value="<?= $type_key; ?>" <?= $type_key == $attribute['type'] ? 'selected' : null; ?>><?= $type_name; ?></option>
												<?php endforeach; ?>
											</select>
											
										</div>
										
										<!-- Is Default? -->
										<div class="input__group">
											
											<label class="input__label"><?= lang('Default?', '省略時値', 'hidden'); ?></label>
											
											<?php if( is_numeric($attribute['id']) ): ?>
												<label class="input__radio">
													<input class="input__choice" name="attributes[is_default][<?= $attribute['type']; ?>]" type="radio" value="<?= $attribute['id']; ?>" <?= $attribute['is_default'] ? 'checked' : null; ?> />
													<span class="symbol__unchecked">default</span>
												</label>
											<?php else: ?>
												<label class="input__checkbox">
													<input class="input__choice" name="attributes[is_default][new]" type="checkbox" value="1" />
													<span class="symbol__unchecked">default</span>
												</label>
											<?php endif; ?>
											
										</div>
										
										<!-- Delete -->
										<?php if( is_numeric($attribute['id']) ): ?>
											<div class="input__group">
												<button class="symbol__delete symbol--standalone" name="delete_attribute[]" type="button" value="<?= $attribute['id']; ?>"></button>
											</div>
										<?php endif; ?>
										
									</li>
								<?php endforeach; ?>
								
							</ul>
						<?php endforeach; ?>
						
					</div>
					
				<?php endif; ?>
				
			<?php
		}
	?>
	
	<div class="text text--docked">
		<div class="input__row">
			<div class="input__group any--flex-grow">
				<button class="any--flex-grow" name="submit" type="submit">
					<?= lang( ($is_edit ? 'Edit' : 'Add').' magazine', '叢書を'.($is_edit ? '編集する' : '追加'), 'hidden'); ?>
				</button>
				<?= $is_edit ? '<a class="symbol__magazine" href="'.$magazine['url'].'" style="line-height:2rem;margin-left:0.5rem;">'.( $magazine['romaji'] ? lang($magazine['romaji'], $magazine['name'], 'hidden') : $magazine['name'] ).'</a>' : null; ?>
				<span data-role="status"></span>
			</div>
		</div>
		<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
	</div>
	
</form>