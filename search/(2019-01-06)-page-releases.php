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
	
	$pageTitle = "Advanced search releases";

	$request = str_replace("/search/releases/?", "", $_SERVER["REQUEST_URI"]);
	parse_str($request, $search);
	
	if(!empty($search["start_date"]) || !empty($search["end_date"])) {
		$search["start_date"] = str_replace(["mm-dd", "dd"], ["01-01", "01"], $search["start_date"]);
		$search["end_date"] = str_replace(["mm-dd", "dd"], ["01-01", "01"], $search["end_date"]);
		
		if(empty($search["start_date"]) || empty($search["end_date"])) {
			$search["start_date"] = $search["start_date"] ?: (preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $search["end_date"]) ? $search["end_date"] : null);
			$search["end_date"] = $search["end_date"] ?: (preg_match("/"."\d{4}-\d{2}-\d{2}"."/", $search["start_date"]) ? $search["start_date"] : null);
		}
		
		if(strlen($search["start_date"]) === 4) {
			$search["end_date"] = $search["end_date"] ?: $search["start_date"]."-12-31";
			$search["start_date"] .= "-01-01";
		}
		elseif(strlen($search["start_date"]) === 7) {
			$search["end_date"] = $search["end_date"] ?: $search["start_date"]."-31";
			$search["start_date"] .= "-01";
		}
	}
	
	$search = array_filter($search);
	
	if(is_array($search) && !empty($search)) {
		$release_query["get"] = "list";
		$release_query["limit"] = 100;
		
		foreach($search as $key => $value) {
			$release_query[$key] = $value;
		}
	}
	
	$access_release = new access_release($pdo);
	$releases = $access_release->access_release($release_query);
?>

<div class="col c1">
	<div>
		<h2>
			Advanced search releases
		</h2>
		
		<form action="/search/releases/#result" enctype="multipart/form-data" method="get" name="form__search-release">
			<div class="text">
				<h3>
					Name
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<input class="any--flex-grow" name="release_name" placeholder="name" value="<?php echo sanitize($search["release_name"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Both Japanese and romanized titles are ok.
				</div>
				
				<hr />
				
				<h3>
					Artist
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<label class="input__label">
							Artist
						</label>
						<select class="any--flex-grow input" name="artist_id" placeholder="choose artist">
							<option></option>
							<?php
								$access_artist = new access_artist($pdo);
								$artist_list = $access_artist->access_artist(["get" => "list"]);
								if(is_array($artist_list)) {
									foreach($artist_list as $artist) {
										?>
											<option value="<?php echo $artist["id"]; ?>" <?php echo ($search["artist_id"] === $artist["id"] ? "selected" : null); ?>><?php echo $artist["quick_name"]; ?></option>
										<?php
									}
								}
							?>
						</select>
					</div>
					<div class="input__group">
						<label class="input__label">
							Performed as
						</label>
						<input name="artist_display_name" placeholder="performed as" value="<?php echo sanitize($search["artist_display_name"]); ?>" />
					</div>
				</div>
				
				<hr />
				
				<h3>
					Product codes
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<label class="input__label">
							Catalog number
						</label>
						<input name="upc" placeholder="Catalog number" value="<?php echo sanitize($search["upc"]); ?>" />
					</div>
					<div class="input__group any--flex-grow">
						<label class="input__label">
							JAN code
						</label>
						<input name="jan_code" placeholder="JAN code" value="<?php echo sanitize($search["jan_code"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Catalog number searches may be fuzzy (eg. &ldquo;UCCD&rdquo; returns several results), but JAN code searches are exact.
				</div>
				
				<hr />
				<h3>
					Record label
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<select class="input" name="label_id" placeholder="choose label">
							<option></option>
							<?php
								include_once("../php/class-access_label.php");
								$access_labels = new access_label($pdo);
								$label_list = $access_labels->access_label(["get" => "list"]);
								if(is_array($label_list)) {
									foreach($label_list as $label) {
										?>
											<option value="<?php echo $label["id"]; ?>" <?php echo ($search["label_id"] === $label["id"] ? "selected" : null); ?>><?php echo $label["quick_name"]; ?></option>
										<?php
									}
								}
							?>
						</select>
					</div>
				</div>
				<div class="input__row">
					<div class="input__group">
						<input class="input__checkbox" id="any" name="label_involvement" type="radio" value="" <?php echo (empty($search["label_involvement"]) || $search["label_involvement"] === "" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="any">Any involvement</label>
					</div>
					<div class="input__group">
						<input class="input__checkbox" id="label" name="label_involvement" type="radio" value="label" <?php echo ($search["label_involvement"] === "label" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="label">Management</label>
					</div>
					<div class="input__group">
						<input class="input__checkbox" id="publisher" name="label_involvement" type="radio" value="publisher" <?php echo ($search["label_involvement"] === "publisher" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="publisher">Publisher</label>
					</div>
					<div class="input__group">
						<input class="input__checkbox" id="distributor" name="label_involvement" type="radio" value="distributor" <?php echo ($search["label_involvement"] === "distributor" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="distributor">Distributor</label>
					</div>
					<div class="input__group">
						<input class="input__checkbox" id="marketer" name="label_involvement" type="radio" value="marketer" <?php echo ($search["label_involvement"] === "marketer" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="marketer">Marketer</label>
					</div>
					<div class="input__group">
						<input class="input__checkbox" id="manufacturer" name="label_involvement" type="radio" value="manufacturer" <?php echo ($search["label_involvement"] === "manufacturer" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="manufacturer">Manufacturer</label>
					</div>
					<div class="input__group">
						<input class="input__checkbox" id="organizer" name="label_involvement" type="radio" value="organizer" <?php echo ($search["label_involvement"] === "organizer" ? "checked" : null); ?> />
						<label class="symbol__unchecked input__checkbox-label" for="organizer">Organizer</label>
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Default behavior is to search for releases which involve the company in any capacity. This scope may be reduced.
				</div>
					
				<hr />
				
				<h3>
					Release date
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__label">
							Start date
						</label>
						<input data-inputmask="'alias': 'yyyy-mm-dd'" maxlength="10" name="start_date" placeholder="yyyy-mm-dd" size="10" value="<?php echo sanitize($search["start_date"]); ?>" />
					</div>
					<div class="input__group">
						<label class="input__label">
							End date
						</label>
						<input data-inputmask="'alias': 'yyyy-mm-dd'" maxlength="10" name="end_date" placeholder="yyyy-mm-dd" size="10" value="<?php echo sanitize($search["end_date"]); ?>" />
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Only one input needs to be filled if searching for releases from only one day. Otherwise, fill both boxes to search for releases during that range. Partial dates are ok.
				</div>
				
				<hr />
				
				<h3>
					Format
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<label class="input__label">
							Physical medium
						</label>
						<select class="any--flex-grow input" name="medium" placeholder="choose medium">
							<option></option>
							<?php
								foreach(["CD", "CD-R", "DVD", "DVD-R", "VHS", "CT", "MD", "8cm CD", "box set", "digital", "book"] as $medium) {
									?>
										<option value="<? echo $medium; ?>" <?php echo ($search["medium"] === $medium ? "selected" : null); ?> ><?php echo $medium; ?></option>
									<?php
								}
							?>
						</select>
					</div>
					<div class="input__group any--flex-grow">
						<label class="input__label">
							Format
						</label>
						<select class="any--flex-grow input" name="format" placeholder="choose format">
							<option></option>
							<?php
								foreach(["demo", "maxi-single", "single", "mini-album", "full album", "collection", "omnibus", "live recording", "PV", "comment", "privilege", "photography", "other"] as $format) {
									?>
										<option value="<? echo $format; ?>" <?php echo ($search["format"] === $format ? "selected" : null); ?> ><?php echo $format; ?></option>
									<?php
								}
							?>
						</select>
					</div>
				</div>
				<div class="any--weaken-color symbol__help search__note">
					Format searches are fuzzy: eg. &ldquo;CD&rdquo; will include CD-R results, and &ldquo;single&rdquo; will include maxi-single results.
				</div>
					
				<hr />
				
				<h3>
					Credits and notes
				</h3>
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<label class="input__label">
							Search credits/notes for string
						</label>
						<input name="notes" placeholder="search text" value="<?php echo sanitize($search["notes"]); ?>" />
					</div>
				</div>
				<div class="any--margin any--weaken-color symbol__help search__note">
					This searches both the user-supplied notes and the booklet's liner notes for mention of the provided string. (Romanized liner notes aren't typically available, so it's advised to search for Japanese text only.)
				</div>
				
				<hr />
				
				<h3>
					Tags
				</h3>
				<div class="input__row">
						<div class="input__group">
							<label class="input__label">
								Search releases by tag
							</label>
							<input class="input__checkbox" id="tag[0]" name="tag" type="radio" value="" <?php echo (empty($search["tag"]) || $search["tag"] === "" ? "checked" : null); ?> />
							<label class="symbol__unchecked input__checkbox-label" for="tag[0]">any tag</label>
						</div>
						<?php
							$sql_tags = "SELECT * FROM tags_releases ORDER BY friendly ASC";
							$stmt_tags = $pdo->prepare($sql_tags);
							$stmt_tags->execute();
							$rslt_tags = $stmt_tags->fetchAll();
							
							if(is_array($rslt_tags) && !empty($rslt_tags)) {
								foreach($rslt_tags as $i => $tag) {
									?>
										<div class="input__group">
											<input class="input__checkbox" id="tag[<?php echo $i+1; ?>]" name="tag" type="radio" value="<?php echo $tag["friendly"]; ?>" <?php echo ($search["tag"] === $tag["friendly"] ? "checked" : null); ?> />
											<label class="symbol__unchecked input__checkbox-label" for="tag[<?php echo $i+1; ?>]"><?php echo $tag["name"]; ?></label>
										</div>
									<?php
								}
							}
						?>
					</div>
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

<div class="col c1">
	<span id="result"></span>
</div>

<?php	
	echo $_SESSION['username'] === 'inartistic' ? '<pre>'.print_r($releases, true).'</pre>' : null;
	
	if(!is_array($releases) || empty($releases)) {
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
					if(count($releases) >= 100) {
						?>
							<div class="text text--outlined text--notice symbol__error">
								Over 100 results were found. Consider narrowing your search.
							</div>
						<?php
					}
				?>
				
				<div>
					<input class="input__checkbox" id="sort--date" name="sort" type="radio" checked />
					<label class="search__sort input__checkbox-label" data-dir="down" data-sort="date" data-target="search__item" for="sort--date"><span class="symbol__down-caret"></span> Date</label>
					
					<input class="input__checkbox" id="sort--name" name="sort" type="radio" />
					<label class="search__sort input__checkbox-label" data-dir="up" data-sort="name" data-target="search__item" for="sort--name"><span class="symbol__up-caret"></span> A-Z</label>
					
					<input class="input__checkbox" id="sort--upc" name="sort" type="radio" />
					<label class="search__sort input__checkbox-label" data-dir="up" data-sort="upc" data-target="search__item" for="sort--upc"><span class="symbol__up-caret"></span> UPC</label>
					
					<input class="input__checkbox" id="filter--all" name="filter" type="radio" checked />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="" data-target="" for="filter--all">All</label>
					
					<input class="input__checkbox" id="filter--cd" name="filter" type="radio" />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="cd" data-target="" for="filter--cd">CD</label>
					
					<input class="input__checkbox" id="filter--dvd" name="filter" type="radio" />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="dvd" data-target="" for="filter--dvd">DVD</label>
					
					<input class="input__checkbox" id="filter--other" name="filter" type="radio" />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="other" data-target="" for="filter--other">Other</label>
					
					<div class="search__clear"></div>
					
					<div class="text text--outlined search__results">
						<table>
							<tbody>
							<?php
								foreach($releases as $release) {
									ob_start();
									?>
										<tr class="search__item" data-date="<?php echo $release["date_occurred"]; ?>" data-name="<?php echo $release["artist"]["quick_name"]." ".$release["quick_name"]; ?>" data-medium="<?php echo strtolower($release["medium"]); ?>" data-upc="<?php echo $release["upc"]; ?>">
											<td class="any--weaken-color search__date">
												<?php echo $release["date_occurred"]; ?>
											</td>
											<td class="search__artist">
												<a class="artist" href="/artists/<?php echo $release["artist"]["friendly"]; ?>/">
													<?php echo $release["artist"]["quick_name"]; ?>
												</a>
											</td>
											<td class="search__title">
												<a class="symbol__release" href="/releases/<?php echo $release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]; ?>/">
													<?php echo $release["quick_name"]; ?>
												</a>
											</td>
											<td class="search__upc any--weaken-color">
												<?php echo $release["upc"] ?: "-"; ?>
											</td>
										</tr>
									<?php
									$line = ob_get_clean();
									$line = str_replace(["\r", "\n", "\t"], "", $line);
									$line = preg_replace("/"."\s+"."/", " ", $line);
									$line = $line."\n";
									echo $line;
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