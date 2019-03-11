<?php
	if($_SESSION["loggedIn"]) {
		script([
			"/scripts/external/script-autosize.js",
			"/scripts/external/script-selectize.js",
			"/scripts/script-initDelete.js",
			"/scripts/script-initSelectize.js",
			"/scripts/script-uploadImage.js",
			"/blog/script-page-update.js",
		]);
		
		style([
			"/style/external/style-selectize.css",
			"/style/style-selectize.css",
			"/blog/style-page-update.css"
		]);
		
		$access_artist = new access_artist($pdo);
		
		$artist_list = $access_artist->access_artist(["get" => "name"]);
		foreach($artist_list as $key => $tmp_artist) {
			$artist_list[$tmp_artist["id"]] = [$tmp_artist["id"], $tmp_artist["friendly"], $tmp_artist["quick_name"].($tmp_artist["romaji"] ? " (".$tmp_artist["name"].")" : null)];
		}
		
		?>
			<div class="col c1">
				<div>
					<h1>
						Blog
					</h1>
				</div>
			</div>
			<div class="col c1">
				<div class="any--flex any--margin">
					<?php
						if(is_array($entry["prev_next"])) {
							foreach($entry["prev_next"] as $prev_next) {
								?>
									<div class="any--flex-grow any--no-wrap <?php echo $prev_next["type"] === "next" ? "any--align-right" : null; ?>">
										<a class="" href="/blog/<?php echo $prev_next["friendly"]; ?>/edit/">
											<?php echo $prev_next["type"] === "prev" ? '<span class="symbol__previous"></span>' : null; ?>
											<?php echo $prev_next["title"]; ?>
											<?php echo $prev_next["type"] === "next" ? '<span class="symbol__next"></span>' : null; ?>
										</a>
									</div>
								<?php
							}
						}
					?>
				</div>
			</div>
			
			<form action="/blog/function-update.php" class="col c3-AAB" enctype="multipart/form-data" method="post" name="form__update">
				<div>
					<span data-contains="artists" hidden><?php echo json_encode(array_values($artist_list)); ?></span>
					<input data-get="id" data-get-into="value" name="id"  value="<?php echo $entry["id"]; ?>" type="hidden" />
					<input data-get="friendly" data-get-into="value" name="friendly" type="hidden" value="<?php echo $entry["friendly"]; ?>" />
					
					<h2 class="update__header">
						<?php echo $entry ? "Edit" : "Add"; ?> entry
					</h2>
					<div class="text">
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Title</label>
								<input class="any--flex-grow" name="title" placeholder="title" value="<?php echo $entry["title"]; ?>" />
							</div>
						</div>
						
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Entry content</label>
								<textarea class="input__textarea any--flex-grow autosize" name="content" placeholder="blog entry here..."><?php echo $entry["content"]; ?></textarea>
							</div>
						</div>
					</div>
					
					<h3>
						Upload image
					</h3>
					<div class="text text--outlined">
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<input class="any--flex-grow" name="images" type="file" multiple />
							</div>
						</div>
						<ul class="image__results">
							<?php
								function image_template($input = []) {
									$n = -1;
									?>
										<li class="image__template <?php $n++; echo $input[$n]; ?>">
											<div class="any--flex">
												<div class="image__image" data-get="image_style" data-get-into="style" style="<?php $n++; echo $input[$n]; ?>">
													<span class="image__status"></span>
												</div>
												<div class="any--flex-grow image__data">
													<input data-get="image_id" data-get-into="value" name="image_id" value="<?php $n++; echo $input[$n]; ?>" hidden />
													
													<div class="input__row">
														<div class="input__group any--flex-grow">
															<label class="input__label">Image code</label>
															<span class="image__result symbol__copy" data-get="image_markdown"><?php $n++; echo $input[$n]; ?></span>
														</div>
														<div class="input__group">
															<input class="input__checkbox" data-get="image_id" data-get-into="value" name="image_is_entry_default" type="radio" value="<?php $n++; echo $input[$n]; ?>" <?php $n++; echo $input[$n]; ?> />
															<label class="input__checkbox-label symbol__unchecked">Main entry image?</label>
														</div>
														<div class="input__group">
															<label class="input__checkbox-label symbol__trash symbol--standalone image__delete" data-get="image_id" data-get-into="data-id" data-id="<?php $n++; echo $input[$n]; ?>"></label>
														</div>
													</div>
													
													<div class="input__row">
														<div class="input__group any--flex-grow">
															<label class="input__label">Description</label>
															<input class="any--flex-grow" name="image_description" value="<?php $n++; echo $input[$n]; ?>" />
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
													
													<div class="input__row">
														<div class="input__group any--flex-grow">
															<label class="input__label">Credit</label>
															<input class="any--flex-grow" name="image_credit" placeholder="[somebody](https://website.com/)" value="<?php $n++; echo $input[$n]; ?>" />
														</div>
														<div class="input__group">
															<input class="input__checkbox" name="image_is_exclusive" type="checkbox" value="1" <?php $n++; echo $input[$n]; ?> />
															<label class="input__checkbox-label symbol__unchecked">vkgy exclusive?</label>
														</div>
													</div>
												</div>
											</div>
										</li>
									<?php
								}

								image_template([
									"any--hidden",
									"",
									"",
									"",
									"",
									"checked",
									"",
									"",
									"",
									"",
									"",
									"",
									"",
									"",
									""
								]);
								
								if(!empty($entry["image"]) && image_exists($entry["image"], $pdo)) {
									$image_artist = "";
									foreach(array_filter(array_unique(explode("(", str_replace(")", "", $entry["image_artist_id"])))) as $tmp_image_artist) {
										$image_artist .= '<option data-name="'.$artist_list[$tmp_image_artist][2].'" value="'.$tmp_image_artist.'" selected>'.$artist_list[$tmp_image_artist][2].'</option>';
									}
									
									image_template([
										"",
										"background-image: url(".$entry["image"].")",
										$entry["image_id"],
										"![".$entry["image_description"]."](http://weloveucp.com".$entry["image"].")",
										$entry["image_id"],
										"checked",
										$entry["image_id"],
										$entry["image_description"],
										$image_artist,
										$entry["image_credit"],
										$entry["image_is_exclusive"] ? "checked" : ""
									]);
								}
							?>
							</ul>
					</div>
					
					<h3>
						Tags
					</h3>
					<div class="text text--outlined update__tags">
						<?php
							if(is_array($tags)) {
								foreach($tags as $tag_key => $tag) {
									if(is_array($entry['tags']) && !empty($entry['tags'])) {
										foreach($entry['tags'] as $key => $current_tag) {
											if($current_tag['id'] === $tag['id']) {
												$tag['checked'] = true;
												unset($entry['tags'][$key]);
												break;
											}
										}
									}
									
									//$tag["checked"] = is_array($entry["tags"]) && in_array($tag["id"], array_keys($entry["tags"])) ? "checked" : null;
									?>
										<input class="input__checkbox" id="<?php echo "tag".$tag_key; ?>" name="tags[]" value="<?php echo $tag["id"]; ?>" type="checkbox" <?php echo $tag["checked"] ? 'checked' : null; ?> />
										<label class="symbol__tag any__tag" for="<?php echo "tag".$tag_key; ?>"><?php echo $tag["tag"]; ?></label>
									<?php
								}
							}
						?>
					</div>
					
					<div class="text text--docked">
						<div class="input__row" data-role="submit-container">
							<div class="input__group any--flex-grow">
								<button class="any--flex-grow" name="submit" type="submit">
									<?php echo $entry ? "Edit" : "Add"; ?> entry
								</button>
								<span data-role="status"></span>
							</div>
							<div class="input__group">
								<label class="input__checkbox-label symbol__trash symbol--standalone" data-get="id" data-get-into="data-id" data-id="<?php echo $entry["id"]; ?>" name="delete"></label>
							</div>
						</div>
						
						<div class="any--flex any--hidden" data-role="edit-container">
							<a class="a--padded a--outlined any--flex-grow any--align-center" data-get="url" data-get-into="href" href="">View entry</a>
							<a class="a--padded" data-get="edit_url" data-get-into="href" data-role="edit">Edit</a>
						</div>
						<div class="text text--outlined text--notice update__result" data-role="result"></div>
					</div>
				</div>
				<div>
					<h3>
						Preview entry
						<span class="update__preview-status"></span>
					</h3>
					<div class="text text--outlined">
						<div class="update__image-container"><img alt="" class="update__image" src="<?php echo $entry["image"]; ?>" /></div>
						<div class="update__preview"></div>
					</div>
				</div>
			</form>
		<?php
	}
?>