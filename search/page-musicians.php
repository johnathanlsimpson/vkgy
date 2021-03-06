<?php
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/external/script-inputmask.js",
		"/scripts/external/script-tinysort.js",
		"/scripts/script-initSelectize.js",
		"/search/script-page-releases.js"
	]);
	
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/search/style-page-releases.css"
	]);
	
	$pageTitle = "Advanced search musicians";

	$request = str_replace("/search/musicians/?", "", $_SERVER["REQUEST_URI"]);
	parse_str($request, $search);
	
	if($search["year"] || $search["month_day"]) {
		$search["birth_date"]  = (preg_match("/"."\d{4}"."/", $search["year"]) ? $search["year"] : "0000")."-";
		$search["birth_date"] .= (preg_match("/"."\d{2}(?:\-\d{2})?"."/", $search["month_day"]) ? $search["month_day"] : "");
		$search["birth_date"]  = preg_replace("/"."[A-z]"."/", "", $search["birth_date"]);
		unset($search["year"], $search["month_day"]);
	}
	
	$search = array_filter($search);
	
	if(is_array($search) && !empty($search)) {
		$access_musician = new access_musician($pdo);
		$musicians = $access_musician->access_musician(array_merge($search, ["get" => "list", "limit" => 50]));
	}
?>

<div class="col c1">
	<div>
		<h2>
			Advanced search: musicians
		</h2>
		
		<form action="/search/musicians/#result" enctype="multipart/form-data" method="get" name="form__search-musician">
			<div class="text">
				<h3>
					Name
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<input class="any--flex-grow" name="name" placeholder="name" value="<?php echo sanitize($search["name"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Both Japanese and romanized names are ok. Real name or stage name ok.
				</div>
				
				<hr />
				
				<h3>
					Gender
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__radio" for="gender_any">
							<input class="input__choice" id="gender_any" name="gender" type="radio" value="" <?php echo (empty($search["gender"]) || $search["gender"] === "" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Any</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="gender_male">
							<input class="input__choice" id="gender_male" name="gender" type="radio" value="1" <?php echo ($search["gender"] === "1" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Male</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="gender_female">
							<input class="input__choice" id="gender_female" name="gender" type="radio" value="2" <?php echo ($search["gender"] === "2" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Female</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="gender_other">
							<input class="input__choice" id="gender_other" name="gender" type="radio" value="3" <?php echo ($search["gender"] === "3" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Other/unknown</span>
						</label>
					</div>
				</div>
				
				<hr />
				
				<h3>
					Position
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__radio" for="position_0">
							<input class="input__choice" id="position_0" name="position" type="radio" value="" <?php echo (empty($search["position"]) || $search["position"] === "" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Any</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="position_1">
							<input class="input__choice" id="position_1" name="position" type="radio" value="1" <?php echo ($search["position"] === "1" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Vocals</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="position_2">
							<input class="input__choice" id="position_2" name="position" type="radio" value="2" <?php echo ($search["position"] === "2" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Guitar</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="position_3">
							<input class="input__choice" id="position_3" name="position" type="radio" value="3" <?php echo ($search["position"] === "3" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Bass</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="position_4">
							<input class="input__choice" id="position_4" name="position" type="radio" value="4" <?php echo ($search["position"] === "4" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Drums</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="position_5">
							<input class="input__choice" id="position_5" name="position" type="radio" value="5" <?php echo ($search["position"] === "5" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Keys</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="position_6">
							<input class="input__choice" id="position_6" name="position" type="radio" value="6" <?php echo ($search["position"] === "6" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Other</span>
						</label>
					</div>
				</div>
				
				<hr />
				
				<h3>
					Blood type
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__radio" for="blood_type_any">
							<input class="input__choice" id="blood_type_any" name="blood_type" type="radio" value="" <?php echo (empty($search["blood_type"]) || $search["blood_type"] === "" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Any</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="blood_type_a">
							<input class="input__choice" id="blood_type_a" name="blood_type" type="radio" value="a" <?php echo ($search["blood_type"] === "a" ? "checked" : null); ?> />
							<span class="symbol__unchecked">A</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="blood_type_b">
							<input class="input__choice" id="blood_type_b" name="blood_type" type="radio" value="b" <?php echo ($search["blood_type"] === "b" ? "checked" : null); ?> />
							<span class="symbol__unchecked">B</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="blood_type_ab">
							<input class="input__choice" id="blood_type_ab" name="blood_type" type="radio" value="ab" <?php echo ($search["blood_type"] === "ab" ? "checked" : null); ?> />
							<span class="symbol__unchecked">AB</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="blood_type_o">
							<input class="input__choice" id="blood_type_o" name="blood_type" type="radio" value="o" <?php echo ($search["blood_type"] === "o" ? "checked" : null); ?> />
							<span class="symbol__unchecked">O</span>
						</label>
					</div>
					<div class="input__group">
						<label class="input__radio" for="blood_type_other">
							<input class="input__choice" id="blood_type_other" name="blood_type" type="radio" value="other" <?php echo ($search["blood_type"] === "other" ? "checked" : null); ?> />
							<span class="symbol__unchecked">Other</span>
						</label>
					</div>
				</div>
					
				<hr />
				
				<h3>
					Birth date
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__label">
							Year
						</label>
						<input data-inputmask="'alias': '9999', 'placeholder' : 'yyyy'" maxlength="4" name="year" placeholder="yyyy" size="4" value="<?php echo sanitize($search["year"]); ?>" />
					</div>
					<div class="input__group">
						<label class="input__label">
							Month and day
						</label>
						<input data-inputmask="'alias': '99-99', 'placeholder' : 'mm-dd'" maxlength="5" name="month_day" placeholder="mm-dd" size="5" value="<?php echo sanitize($search["month_day"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Year is not required. Day can be left blank.
				</div>
				
				<hr />
				
				<h3>
					Birthplace
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<input class="any--flex-grow" name="birthplace" placeholder="name" value="<?php echo sanitize($search["birthplace"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Both Japanese and romanized areas are ok.
				</div>
				
				<hr />
				
				<h3>
					History
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<input class="any--flex-grow" name="history" placeholder="name" value="<?php echo sanitize($search["history"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note any--margin">
					Search for bands that musician was in (or any record label that musician was president of). Japanese or romaji ok.
				</div>
				
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<button class="any--flex-grow">
							Submit search
						</button>
					</div>
					<div class="input__group search__new">
						<a class="" href="/search/releases/">Clear search</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
	if(!is_array($musicians) || empty($musicians)) {
		if(!empty($search)) {
			?>
				<div class="col c1">
					<div>
						<div class="text text--outlined text--error symbol__error">
							Sorry, no results were found for <?php foreach($search as $key => $value) { echo ' <label class="any__note">'.$key.': '.sanitize(is_array($value) ? implode(", ", $value) : $value).'</label>'; } ?>.
						</div>
					</div>
				</div>
			<?php
		}
	}
	else {
		?>
			<div class="col c1">
				<div id="result">
					<h2>
						Results for <?php foreach($search as $key => $value) { echo ' <label class="any__note">'.$key.': '.sanitize(is_array($value) ? implode(", ", $value) : $value).'</label>'; } ?>
					</h2>
				</div>
				
				<?php
					if(count($musicians) >= 50) {
						?>
							<div class="text text--outlined text--notice symbol__error">
								Over 50 results were found. Consider narrowing your search.
							</div>
						<?php
					}
				?>
				
				<div>
					<div class="text text--outlined search__results">
						<table>
							<tbody>
								<?php
									foreach($musicians as $musician) {
										?>
											<tr class="search__item">
												<td class="">
													<a class="musician" href="/musicians/<?php echo $musician["id"]."/".$musician["friendly"]; ?>/">
														<?php echo $musician["quick_name"]; ?>
													</a>
													<span class="any--weaken-color"><?php echo $musician["romaji"] ? "(".$musician["name"].")" : null; ?></span>
													<?php
														if(is_array($musician["hints"])) {
															foreach($musician["hints"] as $hint) {
																?>
																	<span class="any__note"><?php echo $hint; ?></span>
																<?php
															}
														}
													?>
												</td>
											</tr>
										<?php
									}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		<?php
	}
?>