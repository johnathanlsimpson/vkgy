<?php
	
	include_once('../php/function-render_json_list.php');
	
	script([
		'/scripts/external/script-autosize.js',
		'/scripts/external/script-selectize.js',
		'/scripts/external/script-tribute.js',
		'/scripts/external/script-inputmask.js',
		
		'/scripts/script-initDelete.js',
		'/scripts/script-initSelectize.js',
		'/scripts/script-initTribute.js',
		
		'/lives/script-page-edit-live.js',
	]);
	
	style([
		'/style/external/style-tribute.css',
		'/style/external/style-selectize.css',
		'/style/style-selectize.css',
		
		'/lives/style-page-edit-live.css',
	]);
	
	subnav([
		'Edit live' => '/lives/'.sanitize($_GET['id']).'/',
	]);
	
	if($_SESSION['can_add_data']) {
		if(is_array($live) && !empty($live)) {
			$edit_is_allowed = true;
		}
		else {
			$error = 'Sorry, that live doesn\'t exist.';
		}
	}
	else {
		$error = 'Sorry, only administrators may edit lives.';
	}

	if($edit_is_allowed) {
		?>
			<form action="/lives/function-update_live.php" enctype="multipart/form-data" id="form__edit" method="post" name="form__edit">
				<div class="col c1">
					
					<h2>
						<?= lang('Edit live', 'ライブ編集', 'div'); ?>
					</h2>
					
					<div class="text">
						<?php render_json_list('livehouse'); ?>
						
						<input id="form__changes" name="changes" type="hidden" hidden />
						<input name="id" type="hidden" value="<?= $live['id']; ?>" hidden />
						
						<ul>
							<li>
								<div class="input__row">
									
									<div class="input__group">
										<div class="input__label">
											Date
										</div>
										<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_occurred" placeholder="yyyy-mm-dd" size="10" value="<?= $live['date_occurred'] !== '0000-00-00' ? $live['date_occurred'] : null; ?>" />
									</div>
									
									<div class="input__group any--flex-grow">
										<label class="input__label">Livehouse</label>
										<select class="input" name="livehouse_id" placeholder="choose a livehouse" data-source="livehouses">
											<option></option>
											<?php
												if(is_numeric($live['livehouse_id'])) {
													?>
														<option value="<?= $live['livehouse_id']; ?>" selected></option>
													<?php
												}
											?>
										</select>
									</div>
								</div>
								
								<div class="input__row">
									<div class="input__group any--flex-grow">
										<span class="input__label">
											Lineup
										</span>
										<textarea class="input input__textarea any--tributable autosize" name="lineup" placeholder="Band name<?= "\n"; ?>(1)/Megaromania/"><?php
											if(is_array($live['artists']) && !empty($live['artists'])) {
												$num_artists = count($live['artists']);
												
												for($i=0; $i<$num_artists; $i++) {
													if(is_numeric($live['artists'][$i]['id'])) {
														echo '('.$live['artists'][$i]['id'].')/'.($live['artists'][$i]['romaji'] ?: $live['artists'][$i]['name']).'/';
													}
													else {
														echo $live['artists'][$i]['name'];
													}
													
													echo $i + 1 < $num_artists ? "\n" : null;
												}
											}
										?></textarea>
									</div>
								</div>
								
								<div class="input__row">
									<div class="input__group any--flex-grow">
										<span class="input__label">
											Title
										</span>
										<input class="input" name="name" placeholder="event title" />
										<input class="input input--secondary" name="romaji" placeholder="(romaji)" />
									</div>
								</div>
								
							</li>
						</ul>
					</div>
					
					<div class="text text--docked">
						<div class="any--flex input__row" data-role="submit-container">
							<div class="input__group any--flex-grow">
								<button class="any--flex-grow" data-role="submit" name="submit" type="submit">
									Submit
								</button>
							</div>
							<div class="input__group">
								<!--<span class="input__checkbox-label symbol__trash" data-role="delete"></span>-->
							</div>
							<span data-role="status"></span>
						</div>
						
						<div class="any--flex any--hidden" data-role="edit-container">
							<a class="any--align-center a--outlined a--padded any--flex-grow symbol__release" data-get="url" data-get-into="href" href="">View live</a>
							<a class="update__edit any--weaken-color a--outlined a--padded symbol__edit" data-get="edit-url" data-get-into="href" data-role="edit" href="">Edit</a>
							<!--<a class="add__edit any--weaken-color a--outlined a--padded symbol__copy" data-role="duplicate" href="/releases/add/">Duplicate</a>-->
						</div>
						
						<div class="text text--outlined text--error symbol__error update__result" data-role="result"></div>
					</div>
				</div>
			</form>
		<?php
	}
	else {
		?>
			<div class="col c1">
				<div class="text text--outlined text--error symbol__error">
					<?= $error; ?>
				</div>
			</div>
		<?php
	}
?>