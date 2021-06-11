<?php

// ========================================================
// Inclusions
// ========================================================

include_once('../php/include.php');
include_once('../php/class-issue.php');
include_once('../php/class-magazine.php');
$access_issue = new issue($pdo);
$access_magazine = new magazine($pdo);

// ========================================================
// States
// ========================================================

// Set flag
$is_edit = $issue ? true : false;

// We'll use these strings a lot
$add_text_en = 'Add issue';
$add_text_ja = '雑誌を追加';
$edit_text_en = 'Edit issue';
$edit_text_ja = '雑誌を編集する';

// Do some weird stuff with the subnav to make sure it shows appropriate text
$text_en    = $is_edit ? $edit_text_en : $add_text_en;
$text_ja    = $is_edit ? $edit_text_ja : $add_text_ja;
$subnav_url = $is_edit ? $issue['url'].'edit/' : '/magazines/add-issue/';

// ========================================================
// Page setup
// ========================================================

script([
	'/scripts/external/script-alpine.js',
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-inputmask.js',
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-tribute.js',
	'/scripts/script-initTribute.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initDelete.js',
	'/scripts/script-triggerChange.js',
	'/magazines/script-page-update_issue.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/external/style-tribute.css',
	'/style/style-selectize.css',
]);

subnav([
	lang($text_en, $text_ja, [ 'primary_attributes' => 'data-add-text="'.$add_text_en.'" data-edit-text="'.$edit_text_en.'"', 'secondary_attributes' => 'data-add-text="'.$add_text_ja.'" data-edit-text="'.$edit_text_ja.'"', 'container' => 'span', 'secondary_class' => 'any--hidden' ]) => $subnav_url,
]);

$page_header = lang('Magazine issues', '雑誌の問題', 'div');

$page_title = $is_edit ? $edit_text_en.' ('.$edit_text_ja.')' : $add_text_en.' ('.$add_text_ja.')';

// ========================================================
// Get additional data
// ========================================================

// Previous issue
$sql_prev = 'SELECT * FROM issues WHERE magazine_id=? AND friendly<? ORDER BY friendly DESC, date_represented DESC, id DESC LIMIT 1';
$stmt_prev = $pdo->prepare($sql_prev);
$stmt_prev->execute([ $issue['magazine_id'], $issue['issue_friendly'] ]);
$rslt_prev = $stmt_prev->fetch();

if( $rslt_prev ) {
	subnav([
		[
			'text' => $issue['romaji'] || $rslt_prev['volume_romaji'] ? lang( ($issue['romaji'] ?: $issue['name']).' '.($rslt_prev['volume_romaji'] ?: $rslt_prev['volume_name']), $issue['name'].' '.$rslt_prev['volume_name'], 'hidden' ) : $issue['name'].' '.$rslt_prev['volume_name'],
			'url' => $issue['magazine_url'].$rslt_prev['id'].'/'.$rslt_prev['friendly'].'/edit/',
			'position' => 'left',
		],
	], 'directional');
}

// Next issue
$sql_next = 'SELECT * FROM issues WHERE magazine_id=? AND friendly>? ORDER BY friendly ASC, date_represented ASC, id ASC LIMIT 1';
$stmt_next = $pdo->prepare($sql_next);
$stmt_next->execute([ $issue['magazine_id'], $issue['issue_friendly'] ]);
$rslt_next = $stmt_next->fetch();

if( $rslt_next ) {
	subnav([
		[
			'text' => $issue['romaji'] || $rslt_next['volume_romaji'] ? lang( ($issue['romaji'] ?: $issue['name']).' '.($rslt_next['volume_romaji'] ?: $rslt_next['volume_name']), $issue['name'].' '.$rslt_next['volume_name'], 'hidden' ) : $issue['name'].' '.$rslt_next['volume_name'],
			'url' => $issue['magazine_url'].$rslt_next['id'].'/'.$rslt_next['friendly'].'/edit/',
			'position' => 'right',
		],
	], 'directional');
}

// Get list of all magazines
$magazines = $access_magazine->access_magazine([ 'get' => 'basics' ]);

// ========================================================
// Transform data
// ========================================================

// Separate volume patterns so we can update them dynamically
foreach($magazines as $magazine) {
	
	// Get text before/after num
	$volume_pattern = explode('{volume}', $magazine['volume_name_pattern']);
	$before_num = $volume_pattern[0];
	$after_num = $volume_pattern[1] ?: null;
	
	// Get romaji before/after num
	$volume_romaji_pattern = explode('{volume}', $magazine['volume_romaji_pattern']);
	$before_num_romaji = $volume_romaji_pattern[0] ?: null;
	$after_num_romaji = $volume_romaji_pattern[1] ?: null;
	
	// Determines num leading zeroes or if text allowed
	$num_digits = $magazine['num_volume_digits'];
	
	// Save onto page so JS can access
	$magazine_patterns[ $magazine['id'] ] = [
		$num_digits,
		$before_num,
		$before_num_romaji,
		$after_num,
		$after_num_romaji,
		$magazine['default_price']
	];
	
	// If this is the magazine to which this issue belongs, clean up the issue's volume name to remove the templatey bits (unless volume is custom)
	if( $magazine['id'] == $issue['magazine_id'] && !$issue['volume_is_custom'] ) {
		
		// Remove tail first, otherwise length will be off
		if( strlen($issue['volume_name']) - strlen($after_num) == strrpos($issue['volume_name'],$after_num) ) {
			$issue['volume_name'] = substr_replace( $issue['volume_name'], '', ( -1 * strlen($after_num) ), strlen($after_num) );
		}
		if( strpos($issue['volume_name'], $before_num) === 0 ) {
			$issue['volume_name'] = substr_replace( $issue['volume_name'], '', 0, strlen($before_num) );
		}
		
		// Then do same for romaji
		if( $issue['volume_romaji'] ) {
			if( strlen($issue['volume_romaji']) - strlen($after_num_romaji ?: $after_num) == strrpos($issue['volume_romaji'],($after_num_romaji ?: $after_num)) ) {
				$issue['volume_romaji'] = substr_replace( $issue['volume_romaji'], '', ( -1 * strlen($after_num_romaji ?: $after_num) ), strlen($after_num_romaji ?: $after_num) );
			}
			if( strpos($issue['volume_name'], ($before_num_romaji ?: $before_num)) === 0 ) {
				$issue['volume_romaji'] = substr_replace( $issue['volume_romaji'], '', 0, strlen($before_num_romaji ?: $before_num) );
			}
		}
		
	}
	
	// If this is the magazine referenced in the URL, set a flag to say so
	if( $magazine['friendly'] == $_GET['magazine'] ) {
		$issue['magazine_id'] = $magazine['id'];
	}
	
}

?>

<form action="/magazines/function-update_issue.php" class="col c1" enctype="multipart/form-data" method="post" name="update-issue">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<input name="id" data-get="id" data-get-into="value" placeholder="id" type="hidden" value="<?= $issue['id']; ?>" />
	
	<h2 data-add-text="<?= sanitize( lang($add_text_en, $add_text_ja, 'div') ); ?>" data-edit-text="<?= sanitize( lang($edit_text_en, $edit_text_ja, 'div') ); ?>">
		<?= $is_edit ? lang($edit_text_en, $edit_text_ja, 'div') : lang($add_text_en, $add_text_ja, 'div'); ?>
	</h2>
	
	<ul class="text" x-data="{
		showVolume:<?= strlen($issue['magazine_id']) ? 1 : 0; ?>,
		customVolume:<?= $issue['volume_is_custom'] ? 1 : 0; ?>,
		magazine:'<?= is_numeric($issue['magazine_id']) ? $issue['magazine_id'] : null; ?>',
		patterns:'',
		volume:'<?= $issue['volume_name']; ?>',
		volumeRomaji:'<?= $issue['volume_romaji']; ?>'
	}" x-init="patterns=JSON.parse($refs.volumePatterns.innerHTML);">

		<template data-contains="volume-patterns" x-ref="volumePatterns" ><?= json_encode($magazine_patterns); ?></template>
		
		<!-- Magazines -->
		<li class="input__row">
			<div class="input__group any--flex-grow">
				
				<label class="input__label"><?= lang('Magazines', '叢書', 'hidden'); ?></label>
				
				<!-- Magazines -->
				<select class="input any--flex-grow" data-persist-on-dupe name="magazine_id" placeholder="magazine" x-on:change="showVolume=magazine.length;" x-model="magazine">
					<option value=""></option>
					<?php foreach($magazines as $magazine): ?>
					<option value="<?= $magazine['id']; ?>" <?= $magazine['id'] == $issue['magazine_id'] || $magazine['id'] == $_GET['magazine'] ? 'selected' : null;?> ><?= $magazine['romaji'] ? $magazine['romaji'].' ('.$magazine['name'].')' : $magazine['name']; ?></option>
					<?php endforeach; ?>
				</select>
				
			</div>
		</li>
		
		<!-- Volume -->
		<li class="input__row" x-show="showVolume">
			<div class="input__group any--flex-grow">
				
				<label class="input__label"><?= lang('Volume', '号', 'hidden'); ?></label>
				
				<!-- Japanese label -->
				<span class="any__note" style="margin-bottom:0.25rem;margin-right:1ch;" x-show="!customVolume && magazine && ( patterns[magazine][2] || patterns[magazine][4] || volumeRomaji )">name</span>
				
				<!-- Before volume -->
				<span style="line-height:2rem;white-space:pre;" x-text="!customVolume && magazine ? patterns[magazine][1] : null"></span>
				
				<!-- Volume -->
				<input name="volume_name" value="<?= $issue['volume_name']; ?>" :class="{ 'volume--number': !customVolume && magazine && patterns[magazine][0] }" :placeholder="!customVolume && magazine && patterns[magazine][0] > 0 ? '123456'.substr(0,patterns[magazine][0]) : 'volume'" :size="!customVolume && magazine && patterns[magazine][0] > 0 ? 4 : 6" x-model="volume" />
				<input class="input--secondary" name="volume_romaji" placeholder="(romaji)" value="<?= $issue['volume_romaji']; ?>" x-show="customVolume || !(magazine && patterns[magazine][0] > 0)" x-model="volumeRomaji" />
				
				<!-- After volume -->
				<span style="line-height:2rem;white-space:pre;" x-text="!customVolume && magazine ? patterns[magazine][3] : null"></span>
				
				<!-- Romaji label -->
				<div class="input__note any--weaken-color any--flex" x-show="!customVolume && magazine && ( patterns[magazine][2] || patterns[magazine][4] || volumeRomaji )">
					
					<span class="any__note" style="align-self:center;margin-right:1ch;">romaji</span>
					<span style="white-space:pre;" x-text="magazine ? ( patterns[magazine][2] ? patterns[magazine][2] : patterns[magazine][1] ) : ''"></span>
					<span style="white-space:pre;" x-text="magazine ? ( volumeRomaji ? volumeRomaji : volume ) : ''"></span>
					<span style="white-space:pre;" x-text="magazine ? ( patterns[magazine][4] ? patterns[magazine][4] : ( patterns[magazine][3] ? patterns[magazine][3] : '' ) ) : ''"></span>
				
				</div>
				
			</div>
			<div class="input__group" style="align-self:flex-start;">
				
				<!-- Custom volume name -->
				<label class="input__label"><?= lang('Custom', 'カスタム', 'hidden'); ?></label>
				<label class="input__checkbox">
					<input class="input__choice" name="volume_is_custom" type="checkbox" value="1" x-model="customVolume" <?= $issue['volume_is_custom'] ? 'checked' : null; ?> />
					<span class="symbol__unchecked">custom volume?</span>
				</label>
				
			</div>
		</li>
		
		<!-- Dates and price -->
		<li class="input__row">
			
			<!-- Date covered -->
			<div class="input__group">
				
				<label class="input__label">date</label>
				<input class="input" data-inputmask="'alias': '9999-99'" maxlength="7" name="date_represented" placeholder="yyyy-mm" size="7" value="<?= $issue['date_represented']; ?>" />
				
			</div>
			
			<!-- Price -->
			<div class="input__group">
				
				<label class="input__label">price</label>
				<input class="input" data-persist-on-dupe name="price" placeholder="123 yen" size="10" value="<?= $issue['price']; ?>" :value="magazine && patterns[magazine][5] ? patterns[magazine][5] : ''" />
				
			</div>
			
			<!-- Product num -->
			<div class="input__group">
				
				<label class="input__label">catalog num</label>
				<input name="product_number" placeholder="UCCD-001" size="10" value="<?= $issue['product_number']; ?>" />
				
			</div>
			
		</li>

	</ul>
	
	<h3>
		<?= lang('Images', '画像', 'div'); ?>
	</h3>
	
	<div class="text text--outlined symbol__error" style="margin-bottom:1rem;">
		Please do not upload interviews or features&mdash;only covers and flyers are allowed.
	</div>
	
	<?php
		include('../images/function-render_image_section.php');
		render_image_section( $issue['images'], [
			'item_type'     => 'issue',
			'item_id'       => $issue['id'],
			'item_name'     => ( $magazine['romaji'] ?: $magazine['name'] ),
			'description'   => sanitize( ( $magazine['romaji'] ?: $magazine['name'] ).' cover', 'alpine' ),
			'default_id'    => $issue['image_id'],
			'hide_selects'  => true,
			'hide_markdown' => true,
		]);
	?>
	
	<h3>
		<?= lang('Artists', 'アーティスト', 'div'); ?>
	</h3>
	
	<ul class="text">
		
		<li class="input__row">
			
			<!-- Cover -->
			<div class="input__group any--flex-grow" style="flex-basis:400px;align-self:flex-start;">
				
				<label class="input__label">cover artists</label>
				<textarea class="input__textarea autosize any--tributable any--flex-grow" name="is_cover" placeholder="artists on cover"><?= $issue['artists_text']['is_cover']; ?></textarea>
				
			</div>
			
			<!-- Large -->
			<div class="input__group any--flex-grow" style="flex-basis:400px;align-self:flex-start;">
				
				<label class="input__label">large features (2+ pages)</label>
				<textarea class="input__textarea autosize any--tributable any--flex-grow" name="is_large" placeholder="artists with large features"><?= $issue['artists_text']['is_large']; ?></textarea>
				
			</div>
			
		</li>
		
		<li class="input__row">
			
			<!-- Normal -->
			<div class="input__group" style="flex-basis:400px;align-self:flex-start;flex-grow:1;">
				
				<label class="input__label">other appearances (1 page or less)</label>
				<textarea class="input__textarea autosize any--tributable any--flex-grow" name="is_normal" placeholder="other artists"><?= $issue['artists_text']['is_normal']; ?></textarea>
				
			</div>
			
			<!-- Flyers -->
			<div class="input__group" style="flex-basis:400px;align-self:flex-start;flex-grow:1;">
				
				<label class="input__label">flyers</label>
				<textarea class="input__textarea autosize any--tributable any--flex-grow" name="is_flyer" placeholder="artists with solo flyers"><?= $issue['artists_text']['is_flyer']; ?></textarea>
				
			</div>
			
		</li>
		
	</ul>
	
	<h3>
		<?= lang('Additional info', '追加情報', 'div'); ?>
	</h3>
	
	<ul class="text text--outlined">
		
		<li class="input__row">
			
			<!-- Date released -->
			<div class="input__group">
				
				<label class="input__label">released</label>
				<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" maxlength="10" name="date_occurred" placeholder="yyyy-mm-dd" size="10" value="<?= $issue['date_occurred']; ?>" />
				
			</div>
			
			<!-- Friendly -->
			<div class="input__group any--flex-grow">
				
				<label class="input__label"><?= lang('Friendly', 'スラッグ', 'hidden'); ?></label>
				<input name="friendly" placeholder="friendly url" value="<?= $issue['issue_friendly']; ?>" />
				
			</div>
			
			<!-- JAN code -->
			<div class="input__group">
				
				<label class="input__label"><?= lang('JAN code', 'JANコード', 'hidden'); ?></label>
				<input name="jan_code" placeholder="code" value="<?= $issue['jan_code']; ?>" />
				
			</div>
			
		</li>
		
		<li class="input__row">
			<div class="input__group any--flex-grow">
				
				<label class="input__label"><?= lang('Notes', 'ノート', 'hidden'); ?></label>
				<textarea class="input__textarea autosize any--tributable any--flex-grow" name="notes" placeholder="notes"><?= $issue['notes']; ?></textarea>
				
			</div>
		</li>
		
	</ul>
		
	<!-- Delete -->
	<?php if( $is_edit && $_SESSION['can_delete_data'] ): ?>
		<h3>
			<?= lang('Admin', 'アドミン', 'div'); ?>
		</h3>
		<div class="text text--outlined">
			<div class="input__row">
				
				<div class="input__group">
					<label class="input__label"><?= lang('Order', '順', 'hidden'); ?></label>
					<input name="volume_order" placeholder="123" size="4" value="<?= $issue['volume_order']; ?>" />
				</div>
				
				<div class="input__group">
					<label class="input__label"><?= lang('Delete', '消す', 'hidden'); ?></label>
					<button class="symbol__delete symbol--standalone" name="delete" type="button"></button>
				</div>
				
			</div>
		</div>
	<?php endif; ?>
	
	<style>
		.volume--number.volume--number {
			border-radius: var(--border-radius); 
		}
		[size="1"] {
			max-width: 2ch;
		}
		[size="2"] {
			max-width: 3ch;
		}
		[size="3"] {
			max-width: 4ch;
		}
		[size="4"] {
			max-width: 5ch;
		}
		[size="5"] {
			max-width: 6ch;
		}
	</style>
	
	<!-- Submission controls -->
	<div class="text text--docked">
		
		<!-- Submit -->
		<div class="input__row" data-role="submit-container">
			<div class="input__group any--flex-grow">
				
				<button class="any--flex-grow" data-add-text="<?= sanitize( lang($add_text_en, $add_text_ja, 'hidden') ); ?>" data-edit-text="<?= sanitize( lang($edit_text_en, $edit_text_ja, 'hidden') ); ?>" data-role="submit" name="submit" type="submit">
					<?= $is_edit ? lang($edit_text_en, $edit_text_ja, 'hidden') : lang($add_text_en, $add_text_ja, 'hidden'); ?>
				</button>
				<?= $is_edit ? '<a class="symbol__magazine" href="'.$issue['url'].'" style="line-height:2rem;margin-left:0.5rem;">'.( $issue['volume_romaji'] ? lang($issue['volume_romaji'], $issue['volume_name'], 'hidden') : $issue['volume_name'] ).'</a>' : null; ?>
				<span data-role="status"></span>
				
			</div>
		</div>
		
		<!-- View/edit -->
		<div class="any--flex any--hidden" data-role="edit-container">
			
			<a class="a--outlined a--padded any--align-center any--flex-grow" data-get="url" data-get-into="href" href=""><?= lang('View magazine', 'ページを見る', 'hidden'); ?></a>&nbsp;
			<a class="any--weaken-color a--outlined a--padded symbol__edit" data-get="edit_url" data-get-into="href" data-role="edit" href=""><?= lang('Edit', '再編集', 'hidden'); ?></a>&nbsp;
			<a class="any--weaken-color a--outlined a--padded symbol__copy" data-role="duplicate" href="/magazines/add-issue/"><?= lang('Duplicate', '複製する', 'hidden'); ?></a>
			
		</div>
		
		<!-- Result -->
		<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
		
	</div>
	
</form>