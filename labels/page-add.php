<?php
	if($_SESSION["admin"]) {
		script([
			"/scripts/external/script-selectize.js",
			"/scripts/external/script-inputmask.js",
			"/scripts/script-initSelectize.js",
			"/labels/script-page-add.js"
		]);
		
		style([
			"/style/external/style-selectize.css",
			"/style/style-selectize.css",
			"/labels/style-page-add.css"
		]);
		
		foreach($access_label->access_label(["get" => "list"]) as $key => $label) {
			$label_list[] = [
				$label["id"],
				"",
				$label["quick_name"].($label["romaji"] ? " (".$label["name"].")" : "")
			];
			unset($tmp_label_list[$key]);
		}
		
		?>
			<div class="col c1">
				<form action="/labels/function-add.php" enctype="multipart/form-data" method="post" name="form__add">
					<span class="any--hidden" data-contains="companies" hidden><?php echo json_encode($label_list); ?></span>
					
					<h1>
						Labels
					</h1>
					
					<h2>
						Add labels
					</h2>
					
					<?php
						for($i = 0; $i < 6; $i++) {
							?>
								<div class="text">
									<div class="input__row">
										<div class="input__group any--flex-grow">
											<label class="input__label">Name</label>
											<input class="input" name="name[]" placeholder="name" />
											<input class="input--secondary" name="romaji[]" placeholder="(romaji)" />
										</div>
										<div class="input__group">
											<label class="input__label">Parent company</label>
											<select class="input selectize" data-populate-on-click="true" data-source="companies" name="parent_label_id[]" placeholder="select parent company">
												<option></option>
											</select>
										</div>
									</div>
									
									<div class="input__row">
										<div class="input__group">
											<label class="input__label">President</label>
											<input name="president_id[]" placeholder="ID" size="3" />
										</div>
										<div class="input__group any--flex-grow">
											<input class="input" name="president_name[]" placeholder="name" />
											<input class="input--secondary" name="president_romaji[]" placeholder="(romaji)" />
										</div>
										<!--<div class="input__group">
											<select>
												<option value="1" selected>music label</option>
												<option value="2">book publisher</option>
											</select>
										</div>-->
										<div class="input__group">
											<label class="input__label">Date started</label>
											<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_started[]" placeholder="yyyy-mm-dd" size="10" />
										</div>
										<div class="input__group">
											<label class="input__label">Date ended</label>
											<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_ended[]" placeholder="yyyy-mm-dd" size="10" />
										</div>
									</div>
									
									<div class="input__row">
										<div class="input__group any--flex-grow">
											<!--<label class="input__label">History</label>
											<textarea class="input__textarea any--flex-grow" name="history[]"></textarea>-->
											<label class="input__label">Official links</label>
											<textarea class="input__textarea any--flex-grow" name="official_links[]" placeholder="http://official.com/"></textarea>
										</div>
									</div>
								</div>
							<?php
						}
					?>
					
					<div class="text text--docked">
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<button class="any--flex-grow" name="submit" type="submit">
									Add labels
								</button>
								<span data-role="status"></span>
							</div>
						</div>
						<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
					</div>
				</form>
			</div>
		<?php
	}
	else {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--error symbol__error">
						Sorry, only administrators may add labels.
					</div>
				</div>
			</div>
		<?php
		
		include("../labels/index.php");
	}
?>