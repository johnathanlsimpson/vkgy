<?php
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/external/script-inputmask.js",
		"/scripts/script-initSelectize.js",
		"/search/script-page-releases.js"
	]);
	
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/search/style-page-releases.css"
	]);
	
	$pageTitle = "Advanced search artists";

	$request = str_replace("/search/artists/?", "", $_SERVER["REQUEST_URI"]);
	parse_str($request, $search);
	
	$search = array_filter($search, function($value) { return is_array($value) || strlen($value); });
	
	$search['active'] = (string)$search['active'];
	if(!strlen($search['active'])) {
		unset($search['active']);
	}
	
	if(is_array($search) && !empty($search)) {
		$artist_query["get"] = "name";
		$artist_query["limit"] = 100;
		
		foreach($search as $key => $value) {
			$artist_query[$key] = $value;
		}
	}

	$artist_query['page'] = is_numeric($search['page']) ? $search['page'] : 1;
	$artist_query['page'] = $artist_query['page'] ?: 1;
	$artist_query['offset'] = ($artist_query['page'] - 1) * 100;
	$artist_query['limit'] = 100;
	$artist_query['get'] = 'list';
	$artist_query['limit'] = $artist_query['offset'].','.$artist_query['limit'];
	
	foreach($search as $key => $value) {
		if($key !== 'page' && $key !== 'order') {
			$base_url .= '&'.$key.'='.$value;
		}
	}
	$base_url .= '#result';
	
	$access_artist = new access_artist($pdo);
	$artists = $access_artist->access_artist($artist_query);
	$num_artists = count($artists);
?>

<div class="col c1">
	<div>
		<h2>
			Advanced search artists
		</h2>
		
		<form action="/search/artists/#result" enctype="multipart/form-data" method="get" name="form__search-artist">
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
					Both Japanese and romanized titles are ok.
				</div>
				
				<hr />
				
				<h3>
					Activity
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__label">
							Activity status
						</label>
						
						<input class="input__checkbox" id="active-none" name="active" type="radio" value="" <?php echo !is_numeric($search['active']) ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="active-none">any status</label>
						
						<input class="input__checkbox" id="active-0" name="active" type="radio" value="0" <?php echo $search['active'] === '0' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="active-0">unknown</label>
						
						<input class="input__checkbox" id="active-1" name="active" type="radio" value="1" <?php echo $search['active'] === '1' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="active-1">active</label>
						
						<input class="input__checkbox" id="active-2" name="active" type="radio" value="2" <?php echo $search['active'] === '2' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="active-2">disbanded</label>
						
						<input class="input__checkbox" id="active-3" name="active" type="radio" value="3" <?php echo $search['active'] === '3' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="active-3">paused</label>
						
						<input class="input__checkbox" id="active-4" name="active" type="radio" value="4" <?php echo $search['active'] === '4' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="active-4">semi-active</label>
					</div>
					
					<div class="input__group any--flex-grow">
						<label class="input__label">
							Activity area
						</label>
						
						<select class="input" name="area" placeholder="area of activity">
							<option></option>
							<option value="overseas">overseas (&#28023;&#22806;)</option>
							<option value="japan">Japan (&#26085;&#26412;)</option>
							<?php
								$sql_areas = 'SELECT name, romaji, friendly FROM areas ORDER BY friendly';
								$stmt_areas = $pdo->prepare($sql_areas);
								$stmt_areas->execute();
								$rslt_areas = $stmt_areas->fetchAll();
								
								foreach($rslt_areas as $area) {
									?>
										<option value="<?php echo $area['friendly']; ?>"><?php echo $area['romaji'].' ('.$area['name'].')'; ?></option>
									<?php
								}
							?>
						</select>
					</div>
				</div>
				
				<hr />
				
				<h3>
					Type
				</h3>
				<div class="input__row">
					<div class="input__group">
						<input class="input__checkbox" id="type-none" name="type" type="radio" value="" <?php echo !is_numeric($search['type']) ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-none">any type</label>
						
						<input class="input__checkbox" id="type-0" name="type" type="radio" value="0" <?php echo $search['type'] === '0' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-0"><?php echo lang('unknown', '不明', ['secondary_class' => 'any--hidden']); ?></label>
						
						<input class="input__checkbox" id="type-1" name="type" type="radio" value="1" <?php echo $search['type'] === '1' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-1"><?php echo lang('band', 'バンド', ['secondary_class' => 'any--hidden']); ?></label>
						
						<input class="input__checkbox" id="type-2" name="type" type="radio" value="2" <?php echo $search['type'] === '2' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-2"><?php echo lang('session', 'セッション', ['secondary_class' => 'any--hidden']); ?></label>
						
						<input class="input__checkbox" id="type-3" name="type" type="radio" value="3" <?php echo $search['type'] === '3' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-3"><?php echo lang('alter-ego', '別名義バンド', ['secondary_class' => 'any--hidden']); ?></label>
						
						<input class="input__checkbox" id="type-4" name="type" type="radio" value="4" <?php echo $search['type'] === '4' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-4"><?php echo lang('solo', 'ソロ', ['secondary_class' => 'any--hidden']); ?></label>
						
						<input class="input__checkbox" id="type-5" name="type" type="radio" value="5" <?php echo $search['type'] === '5' ? "checked" : null; ?> />
						<label class="symbol__unchecked input__checkbox-label" for="type-5"><?php echo lang('special', '限定', ['secondary_class' => 'any--hidden']); ?></label>
					</div>
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
				
				<hr />
				
				<h3>
					Tags
				</h3>
				<div class="input__row">
					<div class="input__group">
						<label class="input__label">
							Search artists by tag
						</label>
						
						<?php
							$sql_tags = "SELECT * FROM tags_artists ORDER BY friendly ASC";
							$stmt_tags = $pdo->prepare($sql_tags);
							$stmt_tags->execute();
							$rslt_tags = $stmt_tags->fetchAll();
							
							if(is_array($rslt_tags) && !empty($rslt_tags)) {
								foreach($rslt_tags as $i => $tag) {
									?>
										<input class="input__checkbox" id="tags[<?php echo $i+1; ?>]" name="tags[]" type="checkbox" value="<?php echo $tag["friendly"]; ?>" <?php echo (is_array($search["tags"]) && in_array($tag["friendly"], $search["tags"]) ? "checked" : null); ?> />
										<label class="symbol__unchecked input__checkbox-label" for="tags[<?php echo $i+1; ?>]"><?php echo $tag["romaji"] ?: $tag["name"]; ?></label>
									<?php
								}
							}
						?>
					</div>
				</div>
				
				<hr />
				
				<div class="input__row">
					<div class="input__group any--flex-grow">
						<button class="any--flex-grow">
							Submit search
						</button>
					</div>
					<div class="input__group search__new">
						<a class="" href="/search/artists/">Clear search</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
	if(!is_array($artists) || empty($artists)) {
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
					/*if(count($artists) >= 100) {
						?>
							<div class="text text--outlined text--notice symbol__error">
								Over 100 results were found. Consider narrowing your search.
							</div>
						<?php
					}*/
				?>
					<?php
						if($num_artists >= 100 || (is_numeric($search['page']))) {
							?>
								<div class="col c3 any--weaken-color search__pages">
									<div>
										<?php
											if($search['page'] > 1) {
												?>
													<a class="symbol__previous" href="?page=<?php echo ($artist_query['page'] - 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($artist_query['page'] - 1); ?></a>
												<?php
											}
											else {
												echo 'Page 1';
											}
										?>
									</div>
									<div style="text-align: center;">
										Results <?php echo ($artist_query['offset'] + 1).' to '.($artist_query['offset'] + $num_artists); ?>
									</div>
									<div style="text-align: right;">
										<?php
											if($num_artists >= 100) {
												?>
													<a class="symbol__next" href="?page=<?php echo ($artist_query['page'] + 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($artist_query['page'] + 1); ?></a>
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
				
				<div>
					<div class="search__clear"></div>
					
					<div class="text text--outlined search__results">
						<table>
							<tbody>
							<?php
								foreach($artists as $artist) {
									ob_start();
									?>
										<tr class="search__item" data-name="<?php echo $artist["quick_name"]; ?>">
											<td class="search__title">
												<a class="symbol__artist" href="/artists/<?php echo $artist["friendly"]; ?>/">
													<?php echo $artist["romaji"] ?: $artist["name"]; ?>
												</a>
												<span class="any--weaken"><?php echo $artist["romaji"] ? ' ('.$artist["name"].')' : null; ?></span>
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
					<?php
						if($num_artists >= 100 || (is_numeric($search['page']))) {
							?>
								<div class="col c3 any--weaken-color search__pages">
									<div>
										<?php
											if($search['page'] > 1) {
												?>
													<a class="symbol__previous" href="?page=<?php echo ($artist_query['page'] - 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($artist_query['page'] - 1); ?></a>
												<?php
											}
											else {
												echo 'Page 1';
											}
										?>
									</div>
									<div style="text-align: center;">
										Results <?php echo ($artist_query['offset'] + 1).' to '.($artist_query['offset'] + $num_artists); ?>
									</div>
									<div style="text-align: right;">
										<?php
											if($num_artists >= 100) {
												?>
													<a class="symbol__next" href="?page=<?php echo ($artist_query['page'] + 1).($search['order'] ? '&order='.$search['order'] : null).$base_url; ?>">Page <?php echo ($artist_query['page'] + 1); ?></a>
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
		<?php
	}
?>