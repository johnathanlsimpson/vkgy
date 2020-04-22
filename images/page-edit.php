<?php
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/script-initDelete.js",
		"/scripts/script-initSelectize.js",
		"/scripts/script-uploadImage.js",
		"/images/script-page-add.js"
	]);

	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
	]);
	
	// Get queued images
	$sql_queued_images = "SELECT *, CONCAT('/images/image_files_queued/', id, '.', extension) AS url FROM queued_flyers WHERE artist_id IS NULL ORDER BY RAND() LIMIT 100";
	$stmt_queued_images = $pdo->prepare($sql_queued_images);
	$stmt_queued_images->execute();
	$images = $stmt_queued_images->fetchAll();
	
	// Count queued images
	$sql_count = "SELECT COUNT(*) FROM queued_flyers WHERE artist_id IS NULL";
	$stmt_count = $pdo->prepare($sql_count);
	$stmt_count->execute();
	$rslt_count = $stmt_count->fetchColumn();
	
	// Get artist list
	$access_artist = new access_artist($pdo);
	$artist_list = $access_artist->access_artist(["get" => "list"]);
	for($i = 0; $i < count($artist_list); $i++) {
		$artist_list[$i] = [
			$artist_list[$i]["id"],
			"",
			str_replace(["&#92;", "&#34;"], ["\\", "\""], $artist_list[$i]["quick_name"].($artist_list[$i]["romaji"] ? " (".$artist_list[$i]["name"].")" : "")).($artist_list[$i]["friendly"] != friendly($artist_list[$i]["quick_name"]) ? " (".$artist_list[$i]["friendly"].")" : null)
		];
		
		$artist_keys[$artist_list[$i][0]] = $i + 1;
	}
	array_unshift($artist_list, [0, "", "(omnibus / various artists)"]);
	
	$page_header = 'Edit queued images';
?>

<div class="col c1">
	<div>
		
		<div class="text text--outlined text--notice">
			Showing <span class="any__note"><?php echo count($images); ?></span> of <span class="any__note"><?php echo number_format($rslt_count); ?></span> queued images.
		</div>
		
		<div class="any--hidden">
			<span data-contains="artists" hidden><?php echo json_encode($artist_list); ?></span>
		</div>
		
		<div class="edit__images edit__hidden">
			<div class="text">
				<ul class="image__results">
					<?php
						function image_template($input = []) {
							$n = -1;
							?>
								<li class="image__template <?php $n++; echo $input[$n]; ?>">
									<div class="any--flex">
										<a class="image__image lazy" data-get="image_style" data-get-into="style" href="<?php $n++; echo $input[$n]; ?>" target="_blank" style="display: inline-block; height: 100px; width: 100px; background-size: cover; margin-right: 1rem;" data-src="<?php $n++; echo $input[$n]; ?>">
											<span class="image__status"></span>
										</a>
										<div class="any--flex-grow image__data">
											<input name="image_queued" value="1" hidden />
											<input data-get="image_id" data-get-into="value" name="image_id" value="<?php $n++; echo $input[$n]; ?>" hidden />
											<input name="image_is_exclusive" type="checkbox" value="1" checked hidden />
											
											<div class="input__row">
												<div class="input__group any--flex-grow">
													<label class="input__label">Description</label>
													<input class="any--flex-grow" name="image_description" value="<?php $n++; echo $input[$n]; ?>" />
												</div>
												<div class="input__group">
													<label class="input__radio symbol__trash symbol--standalone image__delete" data-get="image_id" data-get-into="data-id" data-id="<?php $n++; echo $input[$n]; ?>"></label>
												</div>
											</div>
											
											<div class="input__row">
												<div class="input__group any--flex-grow">
													<label class="input__label">Artists</label>
													<select class="input" data-populate-on-click="true" data-multiple="true" data-source="artists" name="image_artist_id" multiple>
														<?php $n++; echo $input[$n]; ?>
													</select>
												</div>
											</div>
										</div>
									</div>
									<div class="image__result"></div>
								</li>
							<?php
						}
						
						image_template([
							"any--hidden",
							"",
							"",
							"",
							"flyer",
							"",
							"",
							"",
						]);
						
						if(is_array($images) && !empty($images)) {
							foreach($images as $image) {
								$image_artist = "";
								foreach(array_filter(array_unique(explode("(", str_replace(")", "", $image["artist_id"])))) as $tmp_image_artist) {
									$image_artist .= '<option data-name="'.$artist_list[$artist_keys[$tmp_image_artist]][2].'" value="'.$tmp_image_artist.'" selected>'.$artist_list[$artist_keys[$tmp_image_artist]][2].'</option>';
								}
								
								image_template([
									"",
									$image["url"],
									str_replace("queued", "queued_thumbnail", $image["url"]), //"background-image: url(".$image["url"].")",
									$image["id"],
									$image["description"] ?: "flyer",
									$image["id"], 
									$image_artist,
								]);
							}
						}
					?>
				</ul>
			</div>
		</div>
	</div>
</div>