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
	
	$pageTitle = "Search releases";

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
		$allowed_orders = [
			'date_occurred_asc' => 'releases.date_occurred ASC',
			'date_occurred_desc' => 'releases.date_occurred DESC',
			'name_asc' => 'order_name ASC',
			'name_desc' => 'order_name DESC',
			'upc_asc' => 'releases.upc ASC',
			'upc_desc' => 'releases.upc DESC',
		];
		
		$search_query['page'] = is_numeric($search['page']) ? $search['page'] : 1;
		$search_query['page'] = $search_query['page'] ?: 1;
		$search_query['offset'] = ($search_query['page'] - 1) * 100;
		$search_query['limit'] = 100;
		$search_query['get'] = 'list';
		$search_query['order'] = in_array($search['order'], array_keys($allowed_orders)) ? $allowed_orders[$search['order']] : 'order_name ASC';	
		$search_query['limit'] = $search_query['offset'].','.$search_query['limit'];
		
		foreach($search as $key => $value) {
			if($key !== 'page' && $key !== 'order') {
				$search_query[$key] = $value;
			}
		}
		
		foreach($search as $key => $value) {
			if($key !== 'page' && $key !== 'order') {
				$base_url .= '&'.$key.'='.$value;
			}
		}
		$base_url .= '#result';
	}
	
	$access_release = new access_release($pdo);
	$releases = $access_release->access_release($search_query);
	if(is_array($releases) && !empty($releases)) {
		$releases = array_values($releases);
	}
	$num_releases = count($releases);
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
						Results for <?php foreach($search as $key => $value) { if($key !== 'page' && $key !== 'order') { echo ' <label class="any__note">'.$key.': '.sanitize(is_array($value) ? implode(", ", $value) : $value).'</label>'; } } ?>
					</h2>
				</div>
				
				<?php
					/*if($num_releases >= 100) {
						?>
							<div class="text text--outlined text--notice symbol__error">
								Over 100 results were found. Consider narrowing your search.
							</div>
						<?php
					}*/
				?>
				
				<div>
					<a href="?order=<?php echo ($search['order'] === 'date_occurred_desc' ? 'date_occurred_asc' : 'date_occurred_desc').$base_url; ?>">
						<label class="search__sort input__checkbox-label <?php echo substr($search['order'], 0, 4) === 'date' ? 'input__checkbox-label--selected' : null; ?> <?php echo $search['order'] === 'date_occurred_asc' ? 'symbol__up-caret' : 'symbol__down-caret'; ?>">Date</label>
					</a>
					<a href="?order=<?php echo ($search['order'] === 'name_asc' ? 'name_desc' : 'name_asc').$base_url; ?>">
						<label class="search__sort input__checkbox-label <?php echo substr($search['order'], 0, 4) === 'name' ? 'input__checkbox-label--selected' : null; ?> <?php echo $search['order'] === 'name_desc' ? 'symbol__down-caret' : 'symbol__up-caret'; ?>">A-Z</label>
					</a>
					<a href="?order=<?php echo ($search['order'] === 'upc_asc' ? 'upc_desc' : 'upc_asc').$base_url; ?>">
						<label class="search__sort input__checkbox-label <?php echo substr($search['order'], 0, 3) === 'upc' ? 'input__checkbox-label--selected' : null; ?> <?php echo $search['order'] === 'upc_desc' ? 'symbol__down-caret' : 'symbol__up-caret'; ?>">UPC</label>
					</a>
					
					<input class="input__checkbox" id="filter--all" name="filter" type="radio" checked />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="" data-target="" for="filter--all">All</label>
					
					<input class="input__checkbox" id="filter--cd" name="filter" type="radio" />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="cd" data-target="" for="filter--cd">CD</label>
					
					<input class="input__checkbox" id="filter--dvd" name="filter" type="radio" />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="dvd" data-target="" for="filter--dvd">DVD</label>
					
					<input class="input__checkbox" id="filter--other" name="filter" type="radio" />
					<label class="search__filter input__checkbox-label symbol__unchecked" data-filter="other" data-target="" for="filter--other">Other</label>
					
					<div class="search__clear"></div>
					
					<?php
						if($num_releases >= 100 || (is_numeric($search['page']))) {
							?>
								<div class="col c3 any--weaken-color search__pages">
									<div>
										<?php
											if($search['page'] > 1) {
												?>
													<a class="symbol__previous" href="?page=<?php echo ($search_query['page'] - 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($search_query['page'] - 1); ?></a>
												<?php
											}
											else {
												echo 'Page 1';
											}
										?>
									</div>
									<div style="text-align: center;">
										Results <?php echo ($search_query['offset'] + 1).' to '.($search_query['offset'] + $num_releases); ?>
									</div>
									<div style="text-align: right;">
										<?php
											if($num_releases >= 100) {
												?>
													<a class="symbol__next" href="?page=<?php echo ($search_query['page'] + 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($search_query['page'] + 1); ?></a>
												<?php
											}
											else {
												echo 'Page '.($search['page'] ?: 1);
											}
										?>
									</div>
								</div>
							<?php
						}
					?>
					
					<div class="text text--outlined search__results">
						<ul class="any--weaken-color">
							<?php
								for($i=0; $i<$num_releases; $i++) {
									?>
										<li class="search__item" data-medium="<?php echo strtolower($releases[$i]['medium']); ?>">
											<?php echo $releases[$i]['date_occurred']; ?>
											<a class="search__artist artist" href="<?php echo '/artists/'.$releases[$i]['artist']['friendly'].'/'; ?>"><?php echo $releases[$i]['artist']['quick_name']; ?></a>
											<a class="search__release symbol__release" href="<?php echo '/releases/'.$releases[$i]['artist']['friendly'].'/'.$releases[$i]['id'].'/'.$releases[$i]['friendly'].'/'; ?>"><?php echo $releases[$i]['quick_name']; ?></a>
											<?php echo $releases[$i]['upc'] ?: '-'; ?>
										</li>
									<?php
								}
							?>
						</ul>
					</div>
					
					<?php
						if($num_releases >= 100 || (is_numeric($search['page']))) {
							?>
								<div class="col c3 any--weaken-color search__pages">
									<div>
										<?php
											if($search['page'] > 1) {
												?>
													<a class="symbol__previous" href="?page=<?php echo ($search_query['page'] - 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($search_query['page'] - 1); ?></a>
												<?php
											}
											else {
												echo 'Page 1';
											}
										?>
									</div>
									<div style="text-align: center;">
										Results <?php echo ($search_query['offset'] + 1).' to '.($search_query['offset'] + $num_releases); ?>
									</div>
									<div style="text-align: right;">
										<?php
											if($num_releases >= 100) {
												?>
													<a class="symbol__next" href="?page=<?php echo ($search_query['page'] + 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($search_query['page'] + 1); ?></a>
												<?php
											}
											else {
												echo 'Page '.($search['page'] ?: 1);
											}
										?>
									</div>
								</div>
							<?php
						}
					?>
				</div>
			</div>
		<?php
	}
?>