<?php
	if($_SESSION["loggedIn"]) {
		script([
			'/scripts/external/script-autosize.js',
			'/scripts/script-initDelete.js',
			'/blog/script-page-update.js',
		]);
		
		style([
			"/blog/style-page-update.css"
		]);
		
		include_once('../php/function-render_json_list.php');
		render_json_list('artist');
		
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
					<?php
						include('../images/function-render_image_section.php');
						render_image_section($entry['images'], [
							'item_type' => 'blog',
							'item_id' => $entry['id'],
							'item_name' => 'blog entry',
							'default_description' => '',
							'default_id' => $entry['image_id'],
							'hide_blog' => '1',
							'hide_labels' => '1',
							'hide_musicians' => '1',
							'hide_releases' => '1',
						]);
					?>
					
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
						<div class="update__image any--weaken" style="<?php echo $entry['default_image'] ? 'background-image:url(/images/'.$entry['default_image']['id'].'.medium.jpg);' : null; ?>"></div>
						<div class="update__preview"></div>
					</div>
				</div>
			</form>
		<?php
	}
?>