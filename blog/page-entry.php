<?php
	if(is_array($entry) && !empty($entry)) {
		
		$page_image = "https://vk.gy".str_replace('.', '.large.', $entry["image"]);
		
		$sql_twitter = "SELECT twitter FROM users WHERE username=? LIMIT 1";
		$stmt_twitter = $pdo->prepare($sql_twitter);
		$stmt_twitter->execute([$entry["username"]]);
		$rslt_twitter = $stmt_twitter->fetchColumn();
		
		if(!empty($rslt_twitter) && preg_match("/"."^[A-z0-9_]+$"."/", $rslt_twitter)) {
			$page_creator = $rslt_twitter;
		}
		
		$page_description = preg_replace("/"."<.*?>"."/", "", strtok($entry["content"], "\n"))." (Continued…)";
		
		$entry_has_image = strlen($entry['image']) && image_exists($entry['image'], $pdo) ? true : false;
		
		// Related: entries with same tag
		if(is_array($entry['tags']) && !empty($entry['tags'])) {
			foreach($entry['tags'] as $tag) {
				if(strpos($tag['friendly'], 'release') !== 0 && strpos($tag['friendly'], 'live') !== 0 && strpos($tag['friendly'], 'auto') !== 0) {
					$tag_types_to_search[] = $tag['id'];
					
					// Related: features
					if($tag['friendly'] === 'feature') {
						$sql_related_entries[] = 'SELECT blog_id, "feature" AS relation_type FROM blog_tags WHERE blog_id != ? AND tag_id=? ORDER BY id DESC LIMIT 12';
						$values_related_entries[] = [$entry['id'], $tag['id']];
					}
				}
			}
			
			if(is_array($tag_types_to_search) && !empty($tag_types_to_search)) {
				$sql_related_entries[] = 'SELECT blog_id, "same-tag" AS relation_type FROM blog_tags WHERE blog_id != ? AND ('.substr(str_repeat('tag_id=? OR ', count($tag_types_to_search)), 0, -4).') ORDER BY id DESC LIMIT 12';
				
				array_unshift($tag_types_to_search, $entry['id']);
				$values_related_entries[] = $tag_types_to_search;
			}
		}
		
		// Related: entries by same artist(s)
		if(is_array($entry['tags_artists']) && !empty($entry['tags_artists'])) {
			foreach($entry['tags_artists'] as $tag) {
				$artist_tags_to_search[] = $tag['id'];
			}
			
			if(is_array($artist_tags_to_search) && !empty($artist_tags_to_search)) {
				$sql_related_entries[] = 'SELECT blog_id, "same-artist" AS relation_type FROM blog_artists WHERE blog_id != ? AND ('.substr(str_repeat('artist_id=? OR ', count($artist_tags_to_search)), 0, -4).') ORDER BY id DESC LIMIT 12';
				
				array_unshift($artist_tags_to_search, $entry['id']);
				$values_related_entries[] = $artist_tags_to_search;
			}
		}
		
		// Related: entries by related artists
		if(is_array($entry['tags_artists']) && !empty($entry['tags_artists'])) {
			foreach($entry['tags_artists'] as $tag) {
				$artists_mentioned_in_entry[] = $tag['id'];
			}
			
			$related_artists = $access_artist->get_related_artists($artists_mentioned_in_entry, 'label');
			
			if(is_array($related_artists) && !empty($related_artists)) {
				foreach($related_artists as $artist) {
					$related_artist_tags_to_search[] = $artist['id'];
				}
			}
			
			if(is_array($related_artist_tags_to_search) && !empty($related_artist_tags_to_search)) {
				$sql_related_entries[] = 'SELECT blog_id, "related-artist" AS relation_type FROM blog_artists WHERE blog_id != ? AND ('.substr(str_repeat('artist_id=? OR ', count($related_artist_tags_to_search)), 0, -4).') ORDER BY id DESC LIMIT 12';
				
				array_unshift($related_artist_tags_to_search, $entry['id']);
				$values_related_entries[] = $related_artist_tags_to_search;
			}
		}
		
		// Get related entries: merge sql queries and values, then randomize and return
		if(is_array($sql_related_entries) && is_array($values_related_entries) && count($sql_related_entries) === count($values_related_entries)) {
			$values_ids_of_related_entries = [];
			
			foreach($values_related_entries as $values_set) {
				$values_ids_of_related_entries = array_merge($values_ids_of_related_entries, $values_set);
			}
			
			$sql_ids_of_related_entries = '
				SELECT blog_id, relation_type FROM
				(('.implode(') UNION (', $sql_related_entries).')) possibilities ORDER BY RAND()
			';
			$stmt_ids_of_related_entries = $pdo->prepare($sql_ids_of_related_entries);
			$stmt_ids_of_related_entries->execute($values_ids_of_related_entries);
			$rslt_ids_of_related_entries = $stmt_ids_of_related_entries->fetchAll();
			
			// For ids of related entries, go back and get actual entry info
			if(is_array($rslt_ids_of_related_entries) && !empty($rslt_ids_of_related_entries)) {
				foreach($rslt_ids_of_related_entries as $related_entry_id) {
					$related_entry_ids[] = $related_entry_id['blog_id'];
				}
				$related_entry_ids = array_values(array_unique($related_entry_ids));
				
				$sql_related_entries = 'SELECT blog.title, blog.friendly, blog.image_id, images.extension FROM blog LEFT JOIN images ON images.id=blog.image_id WHERE '.substr(str_repeat('blog.id=? OR ', count($related_entry_ids)), 0, -4).' ORDER BY RAND() LIMIT 6';
				$stmt_related_entries = $pdo->prepare($sql_related_entries);
				$stmt_related_entries->execute($related_entry_ids);
				$entry['related'] = $stmt_related_entries->fetchAll();
			}
		}
		
		?>
			<article class="row <?php echo $entry_has_image ? null : 'entry--no-image'; ?>">
				<div class="col c4-ABBC entry__head">
					<div class="entry__side">
						<?php
							if($entry['prev_next'][0]['type'] === 'prev') {
								?>
									<h5>
										Older
									</h5>
									<a href="/blog/<?php echo $entry["prev_next"][0]["friendly"]; ?>/" style="display: block;">
										<?php echo $entry["prev_next"][0]["title"]; ?>
									</a>
								<?php
							}
						?>
					</div>
					
					<header class="entry__main-column">
						<time class="h5 entry__date" datetime="<?php echo $entry['date_occurred']; ?>">
							<?php echo $entry['date_occurred']; ?>
						</time>
						<h1 class="entry__title">
							<a class="a--inherit" href="/blog/<?php echo $entry['friendly']; ?>/"><?php echo $entry['title']; ?></a>
						</h1>
					</header>
					
					<div class="entry__side">
						<?php
							if(isset($entry['prev_next'][1])) {
								?>
								<h5>
									Newer
								</h5>
								<a href="/blog/<?php echo $entry["prev_next"][1]["friendly"]; ?>/" style="display: block;">
									<?php echo $entry["prev_next"][1]["title"]; ?>
								</a>
								<?php
							}
						?>
					</div>
				</div>
				
				<div class="col c4-ABBC">
					<aside class="entry__details entry__side">
						<div class="text text--outlined">
							<ul>
								<li class="any--flex">
									<a class="entry__avatar lazy" data-src="<?php echo '/usericons/avatar-'.$entry['username'].'.png'; ?>" href="/users/<?php echo $entry['username']; ?>/"></a>
									<div>
										<h5>
											Written by
										</h5>
										<a class="user" href="/users/<?php echo $entry['username']; ?>/"><?php echo $entry['username']; ?></a>
									</div>
								</li>
								<?php
									if(is_array($entry['edit_history']) && !empty($entry['edit_history'])) {
										foreach($entry['edit_history'] as $edit) {
											if($edit['username'] != $entry['username']) {
												$show_edits = true;
												break;
											}
										}
										
										if($show_edits) {
											?>
												<li>
													<h5>
														Contributors
													</h5>
													<?php
														$shown_users = [];
														
														foreach($entry['edit_history'] as $edit) {
															if(!in_array($edit['username'], $shown_users)) {
																?>
																	<span>
																		<a class="entry__avatar lazy" data-src="<?php echo '/usericons/avatar-'.$edit['username'].'.png'; ?>" href="/users/<?php echo $edit['username']; ?>/"></a>
																	</span>
																<?php
															}
															
															$shown_users[] = $edit['username'];
														}
													?>
												</li>
											<?php
										}
									}
								?>
						</div>
						
						<?php
							if((is_array($entry['tags']) && !empty($entry['tags'])) || (is_array($entry['tags_artists']) && !empty($entry['tags_artists']))) {
								?>
									<div class="text text--outlined">
										<ul>
											<?php
												if(is_array($entry["tags"]) && !empty($entry["tags"])) {
													?>
														<li>
															<h5>
																Tags
															</h5>
															<?php
																foreach($entry["tags"] as $tag) {
																	if($tag['friendly'] === 'auto-generated') {
																		$is_auto_generated = true;
																	}
																	?>
																		<a class="tag symbol__tag" href="/blog/tag/<?php echo $tag["friendly"]; ?>/"><?php echo $tag["name"]; ?></a>
																	<?php
																}
															?>
														</li>
													<?php
												}
												
												if(is_array($entry["tags_artists"]) && !empty($entry["tags_artists"])) {
													?>
														<li>
															<h5>
																Tagged artists
															</h5>
															<?php
																foreach($entry["tags_artists"] as $tag) {
																	?>
																		<a class="tag symbol__tag" href="/blog/artist/<?php echo $tag["friendly"]; ?>/"><?php echo $tag["quick_name"]; ?></a>
																	<?php
																}
															?>
														</li>
													<?php
												}
												
												if($is_auto_generated) {
													?>
														<li class="any--weaken-color">
															<span class="symbol__error"></span>
															This post was automatically generated. Feel free to note any inaccuracies in the comments.
														</li>
													<?php
												}
											?>
										</ul>
									</div>
								<?php
							}
							
							if($_SESSION['username'] === 'inartistic') {
								?>
									<!--<div>
										<div class="fb-page" data-href="https://www.facebook.com/vkgy.official/" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/vkgy.official/" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/vkgy.official/">vk.gy - visual kei library</a></blockquote></div>
										<div id="fb-root"></div>
										<script>(function(d, s, id) {
											var js, fjs = d.getElementsByTagName(s)[0];
											if (d.getElementById(id)) return;
											js = d.createElement(s); js.id = id;
											js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2&appId=947845311963607&autoLogAppEvents=1';
											fjs.parentNode.insertBefore(js, fjs);
										}(document, 'script', 'facebook-jssdk'));</script>
									</div>
									<div>
										<iframe src="https://discordapp.com/widget?id=473742524926918667&theme=dark" width="350" height="500" allowtransparency="true" frameborder="0"></iframe>
									</div>-->
								<?php
							}
						?>
					</aside>
					
					<div class="entry__content entry__main-column">
						<a class="entry__image-link lazy" data-src="<?php echo str_replace('.', '.thumbnail.', $entry['image']); ?>" href="<?php echo $entry['image']; ?>">
							<img class="entry__image lazy" data-src="<?php echo str_replace('.', '.large.', $entry['image']); ?>" />
						</a>
						
						<div class="text text--centered">
							<?php
								echo $entry['content'];
							?>
						</div>
					</div>
					
					<aside class="entry__supplements entry__side">
						<?php
							if(is_array($entry["tags_artists"]) && !empty($entry["tags_artists"])) {
								?>
									<h3>
										<div class="any--en">
											Mentioned artists
										</div>
										<div class="any--jp any--weaken">
											<?php echo sanitize('関連アーティスト'); ?>
										</div>
									</h3>
									
									<div class="card--small">
										<?php
											$i = 0;
											foreach($entry["tags_artists"] as $artist) {
												if(is_array($artist) && !empty($artist) && is_numeric($artist["id"]) && $i < 4) {
													$artist = $access_artist->access_artist(["id" => $artist["id"], "get" => "name"]);
													$access_artist->artist_card($artist);
													$i++;
												}
											}
										?>
									</div>
								<?php
							}
						?>
						
						<?php
							if(is_array($entry['related']) && !empty($entry['related'])) {
								?>
									<h3>
										<div class="any--en">
											Other stories
										</div>
										<div class="any--jp any--weaken">
											<?php echo sanitize('関連ニュース'); ?>
										</div>
									</h3>
									<div class="any--flex" style="flex-wrap: wrap; margin: -0.5rem; margin-bottom: 2.5rem;">
										<?php
											foreach($entry['related'] as $related) {
												?>
													<a class="text text--outlined" href="/blog/<?php echo $related['friendly']; ?>/" style="align-self: flex-start; flex-grow: 1; flex-basis: 150px; margin: 0.5rem; <?php echo $related['image_id'] ? 'padding-top: calc(100px + 1rem); background-size: auto 100px; background-repeat: no-repeat; background-position: top center; background-image: url(/images/'.$related['image_id'].'.small.'.$related['extension'].'), linear-gradient(var(--background), var(--background));' : null; ?>">
														<?php
															/*/ padding-top: calc(100px + 1rem); margin: 0.5rem; align-self: flex-start; flex-grow: 1; flex-basis: 150px; */
															
															echo $related['title'];
														?>
													</a>
												<?php
											}
										?>
									</div>
								<?php
							}
						?>
					</aside>
				</div>
			</article>
			
			<style>
				@media(max-width: 799.9px) {
					.entry__side {
						order: 2;
					}
					.entry__head .entry__side {
						display: none;
					}
				}
				.entry__main-column.entry__main-column {
					max-width: 800px;
				}
				.entry__side.entry__side {
					max-width: none;
				}
				.entry__head .entry__side:last-of-type {
					text-align: right;
				}
				.entry__title, .entry__date {
					display: block;
					margin-left: auto;
					margin-right: auto;
					max-width: 100%;
					width: 600px;
				}
				.entry--no-image .entry__image-container {
					display: none;
				}
				.entry__image-link {
					background-size: 0;
					display: block;
					margin: 0;
					max-height: 80vh;
					overflow: hidden;
					text-align: center;
					width: 100%;
				}
				.entry__image-link::before {
					background-image: inherit;
					background-position: center;
					background-size: cover;
					content: "";
					display: block;
					filter: blur(10px);
					height: 100%;
					left: 0;
					position: absolute;
					top: 0;
					transform: scale(1.1);
					width: 100%;
				}
				.entry__image-link::after {
					background: var(--background--faint);
					content: "";
					display: block;
					height: 100%;
					left: 0;
					opacity: 0.5;
					position: absolute;
					top: 0;
					width: 100%;
				}
				.entry__image {
					max-height: 500px;
					max-width: 100%;
					object-fit: contain;
					vertical-align: middle;
					z-index: 1;
				}
				.entry__avatar {
					background-position: center;
					background-size: cover;
					border: 2px solid var(--background--bold);
					border-radius: 50%;
					display: inline-block;
					height: 50px;
					margin-right: 1rem;
					width: 50px;
				}
				.text--centered p {
					margin-left: auto;
					margin-right: auto;
					max-width: 100%;
					width: 600px;
				}
				.text--centered ul {
					margin-left: auto;
					margin-right: auto;
					max-width: 100%;
					width: 600px;
				}
				.text--centered .module {
					background: var(--background--bold);
				}
			</style>
		<?php
		
		include('../comments/partial-comments.php');
		render_default_comment_section('blog', $entry['id'], $entry['comments'], $markdown_parser);
	}
?>