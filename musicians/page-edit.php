<?php

include_once('../php/function-render_json_list.php');

$access_artist = new access_artist($pdo);

script([
	'/scripts/external/script-alpine.js',
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-tribute.js',
	'/scripts/external/script-inputmask.js',

	'/scripts/script-initDelete.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initTribute.js',

	'/musicians/script-page-edit.js',
]);

style([
	'/style/external/style-tribute.css',
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/musicians/style-page-edit.css'
]);

// Get areas for musicians' hometown
$sql_areas = 'SELECT areas.id, areas.name, areas.romaji, areas.friendly FROM areas ORDER BY areas.friendly ASC';
$stmt_areas = $pdo->prepare($sql_areas);
$stmt_areas->execute();
$areas = $stmt_areas->fetchAll();

render_json_list('area', $areas);

?>

<?php if( $_SESSION['can_add_data'] && ( !$musician_is_removed || ( $musician_is_removed && $_SESSION['can_approve_data'] ) ) ): ?>

	<?php if($error): ?>
		<div class="col c1">
			<div>
				<div class="text text--outlined error symbol__error"><?= $error; ?></div>
			</div>
		</div>
	<?php endif; ?>
	
	<form action="/musicians/function-edit.php" class="col c1 any--margin" enctype="multipart/form-data" method="post" name="edit_musician">
		
		<h2>
			<?= $musician['romaji'] ? lang($musician['romaji'], $musician['name'], 'div') : $musician['name']; ?>
		</h2>
		
		<h3>
			<?= lang('Details', '情報', 'div'); ?>
		</h3>
		
		<ul class="text">
			
			<li class="input__row">
				
				<!-- ID -->
				<div class="input__group">
					<label class="input__label">ID</label>
					<input name="id" size="4" value="<?= $musician['id']; ?>" readonly />
				</div>
				
				<!-- Name -->
				<div class="input__group any--flex-grow">
					<label class="input__label">Name</label>
					<input class="input any--flex-grow" name="name" placeholder="name" value="<?= $musician['name']; ?>" />
					<input class="input--secondary" name="romaji" placeholder="(romaji)" value="<?= $musician['romaji']; ?>" />
				</div>
				
				<div class="input__group">
					<label class="input__label">Friendly</label>
					<input name="friendly" placeholder="friendly name" value="<?= $musician['friendly']; ?>" />
				</div>
				
				<!-- Changes -->
				<input name="changes" type="hidden" />
					
			</li>

			<li class="input__row">
				
				<!-- Position -->
				<div class="input__group">

					<label class="input__label">Usual position</label>

					<?php foreach( $access_artist->positions as $position_key => $position ): ?>
						<label class="input__radio">
							<input class="input__choice" name="usual_position" type="radio" value="<?= $position_key; ?>" <?= $musician['usual_position'] == $position_key ? 'checked' : null; ?> />
							<span class="symbol__unchecked"><?= strtolower($position); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				
			</li>
			
			<li class="input__row">
				
				<!-- Gender -->
				<div class="input__group">

					<label class="input__label">Gender</label>

					<?php foreach([ 'unknown', 'male', 'female', 'other' ] as $gender_key => $gender): ?>
						<label class="input__radio">
							<input class="input__choice" name="gender" type="radio" value="<?= $gender_key; ?>" <?= $musician['gender'] == $gender_key ? 'checked' : null; ?> />
							<span class="symbol__unchecked"><?= $gender; ?></span>
						</label>
					<?php endforeach; ?>

				</div>
				
				<!-- Blood -->
				<div class="input__group">
					<label class="input__label">Blood</label>
					<input name="blood_type" placeholder="eg. B" size="3" value="<?= $musician['blood_type']; ?>" />
				</div>

				<!-- Birth -->
				<div class="input__group">
					<label class="input__label">Birth date</label>
					<input data-inputmask="'alias': '99-99'" max-length="5" name="birth_date" placeholder="mm-dd" size="8" value="<?= substr($musician['birth_date'], 5) ?: null; ?>" />
				</div>

				<div class="input__group">
					<label class="input__label">Birth year</label>
					<input data-inputmask="'alias': '[A99][9999]','greedy':false" max-length="4" name="birth_year" placeholder="yyyy" size="8" value="<?= $musician['birth_date'] > '0001' ? substr($musician['birth_date'], 0, 4) : null; ?>" />
				</div>

					<div class="input__group">
						<label class="input__label">Home area</label>

						<select class="input" data-source="areas" name="birthplace">
							<option value="">unknown</option>
							<?= is_numeric($musician['birthplace']) ? '<option value="'.$musician['birthplace'].'" selected></option>' : null; ?>
						</select>

					</div>
				
			</li>
			
		</ul>
		
		<h3>
			<?= lang('Band history', '歴史', 'div'); ?>
		</h3>
		
		<ul class="text">
			
			<li class="input__row">
				
				<!-- History -->
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Band history</label>
					<textarea class="autoresize input__textarea any--flex-grow any--tributable" name="history" placeholder="(1)[Nega]&#10;(1) (support)"><?= $musician['raw_history']; ?></textarea>
				
				</div>
				
			</li>
			
		</ul>
		
		<div class="text text--docked">

			<div class="input__row" data-role="submit-container">

				<!-- Submit -->
				<div class="input__group any--flex-grow">
					<button class="any--flex-grow" data-role="submit" type="submit">
						Submit edits
					</button>
				</div>
				
				<!-- Link -->
				<a class="a--padded any--weaken-size symbol__arrow-right-circled" href="<?= '/musicians/'.$musician['id'].'/'.$musician['friendly'].'/'; ?>" target="_blank"><?= $musician['romaji'] ? lang($musician['romaji'], $musician['name'], 'hidden') : $musician['name']; ?></a>

				<!-- Status -->
				<span data-role="status" style="margin-top:1rem;"></span>

				<!-- Delete -->
				<?php if( $_SESSION['can_delete_data'] ): ?>
					<div class="input__group">
						<label class="input__radio symbol__trash symbol--standalone" data-id="<?= $musician['id']; ?>" name="delete"></label>
					</div>
				<?php endif; ?>

			</div>

			<div class="edit__result text text--outlined text--notice any--hidden" data-role="result"></div>

		</div>
		
		
		
						
	</form>

<?php endif; ?>