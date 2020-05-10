<?php
	style([
		'/blog/style-page-entry.css',
	]);
	
	// Auto-style translation button
	$entry['content'] = str_replace(
		'>&#9888; &#26085;&#26412;&#35486;&#29256;&#12408;&#12371;&#12385;&#12425;&#12290;</a>',
		' class="symbol__error a--outlined a--padded">&#26085;&#26412;&#35486;&#29256;&#12408;&#12371;&#12385;&#12425;&#12290;</a>',
		$entry['content']);
	$entry['content'] = str_replace(
		'>&#9888; The English version is here.</a>',
		' class="symbol__error a--outlined a--padded">The English version is here.</a>',
		$entry['content']);
	
	if(is_array($entry) && !empty($entry)) {

		$entry['images'] = is_array($entry['images']) ? $entry['images'] : [];

		if(!empty($entry['images']) && is_numeric($entry['image_id'])) {
			$entry['image'] = $entry['images'][$entry['image_id']];

			$page_image = "https://vk.gy".str_replace('.', '.large.', $entry['image']['url']);

			$entry_has_image = true;
		}
		
		// Loop through tags and see if featured article; if so, upsize images
		if(is_array($entry['tags']) && !empty($entry['tags'])) {
			foreach($entry['tags'] as $tag) {
				if($tag['friendly'] === 'feature') {
					$entry_is_feature = true;
					
					break;
				}
			}
		}
		
		// Make blog entries show large versions of images
		$entry['content'] = str_replace('.medium.', '.large.', $entry['content']);
		
		$sql_twitter = "SELECT twitter FROM users WHERE username=? LIMIT 1";
		$stmt_twitter = $pdo->prepare($sql_twitter);
		$stmt_twitter->execute([ $entry['user']['username'] ]);
		$rslt_twitter = $stmt_twitter->fetchColumn();

		if(!empty($rslt_twitter) && preg_match("/"."^[A-z0-9_]+$"."/", $rslt_twitter)) {
			$page_creator = $rslt_twitter;
		}

		$page_description = preg_replace("/"."<.*?>"."/", "", strtok($entry["content"], "\n"))." (Continued…)";
		
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

				$sql_related_entries = 'SELECT blog.title, blog.friendly, blog.image_id, images.extension FROM blog LEFT JOIN images ON images.id=blog.image_id WHERE ('.substr(str_repeat('blog.id=? OR ', count($related_entry_ids)), 0, -4).') AND blog.is_queued=0 ORDER BY RAND() LIMIT 6';
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
								subnav([
									[
										'text' => $entry['prev_next'][0]['title'],
										'url' => '/blog/'.$entry['prev_next'][0]['friendly'].'/',
										'position' => 'left',
									]
								], 'directional');
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
								subnav([
									[
										'text' => $entry['prev_next'][1]['title'],
										'url' => '/blog/'.$entry['prev_next'][1]['friendly'].'/',
										'position' => 'right',
									]
								], 'directional');
							}
						?>
					</div>
				</div>

				<div class="col c4-ABBC">
					<aside class="entry__details entry__side">
						<div class="text text--outlined">
							<ul>
								<li class="any--flex">
									<a class="entry__avatar lazy" data-src="<?= '/usericons/avatar-'.$entry['user']['username'].'.png'; ?>" href="<?= $entry['user']['url']; ?>"></a>
									<div>
										<h5>
											Written by
										</h5>
										<a class="user" data-icon="<?= $entry['user']['icon']; ?>" data-is-vip="<?= $entry['user']['is_vip']; ?>" href="<?= $entry['user']['url']; ?>"><?= $entry['user']['username']; ?></a>
									</div>
								</li>
								<?php
									if(is_array($entry['edit_history']) && !empty($entry['edit_history'])) {
										foreach($entry['edit_history'] as $edit) {
											
											if($edit['user']['username'] != $entry['user']['username']) {
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
															if(!in_array($edit['user']['username'], $shown_users)) {
																?>
																	<span>
																		<a class="entry__avatar lazy" data-src="<?= '/usericons/avatar-'.$edit['user']['username'].'.png'; ?>" href="<?= $edit['user']['url']; ?>"></a>
																	</span>
																<?php
															}

															$shown_users[] = $edit['user']['username'];
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
						?>
					</aside>

					<div class="entry__content entry__main-column">
						<a class="entry__image-link lazy" data-src="<?php echo str_replace('.', '.thumbnail.', $entry['image']['url']); ?>" href="<?php echo $entry['image']['url']; ?>">
							<img class="entry__image webfeedsFeaturedVisual" src="<?= $entry_is_feature ? $entry['image']['url'] : str_replace('.', '.large.', $entry['image']['url']); ?>" />
						</a>
						
						<div class="text text--centered">
							<?php
								echo $entry['content'];
								
								if($entry['sources']) {
									preg_match_all('/'.'^(@([A-z0-9-_]+))(?:\s|$)'.'/m', $entry['sources'], $twitter_matches);
									
									if(is_array($twitter_matches) && !empty($twitter_matches)) {
										for($i=0; $i<count($twitter_matches[0]); $i++) {
											$entry['sources'] = str_replace($twitter_matches[1][$i], '['.$twitter_matches[1][$i].'](https://twitter.com/'.$twitter_matches[2][$i].'/)', $entry['sources']);
										}
									}
									
									$sources = $entry['sources'];
									$sources = explode("\n", $sources);
									$sources = array_filter($sources);
									$sources = (count($sources) > 1 ? '* ' : null).implode("\n* ", $sources);
									$sources = $markdown_parser->parse_markdown($sources);
									$sources = str_replace('<ul class="ul--bulleted">', '<ul class="text text--outlined text--notice entry__sources">', $sources);
									
									?>
										<h5 style="margin-top: 3rem;">
											Sources
										</h5>
										<?= $sources; ?>
									<?php
								}
								
								if($entry['supplemental']) {
									preg_match_all('/'.'^(@([A-z0-9-_]+))(?:\s|$)'.'/m', $entry['supplemental'], $twitter_matches);
									
									if(is_array($twitter_matches) && !empty($twitter_matches)) {
										for($i=0; $i<count($twitter_matches[0]); $i++) {
											$entry['supplemental'] = str_replace($twitter_matches[1][$i], '['.$twitter_matches[1][$i].'](https://twitter.com/'.$twitter_matches[2][$i].'/)', $entry['supplemental']);
										}
									}
									
									$supplemental = $entry['supplemental'];
									$supplemental = explode("\n", $supplemental);
									$supplemental = array_filter($supplemental);
									$supplemental = implode("\n", $supplemental);
									$supplemental = preg_replace('/'.'^([^*])'.'/m', '* $1', $supplemental);
									$supplemental = $markdown_parser->parse_markdown($supplemental);
									$supplemental = str_replace('<ul class="ul--bulleted">', '<ul class="text text--outlined text--notice entry__sources">', $supplemental);
									
									?>
										<h5 style="margin-top: 3rem;">
											Links 
										</h5>
										<?= $supplemental; ?>
									<?php
								}
							?>
						</div>
						
						<?php
							// If entry has associated images, see if we need a separate image gallery
							if(is_array($entry['images']) && !empty($entry['images'])) {
								
								// Copy images array and reset keys
								$image_gallery = array_values($entry['images']);
								$num_images = count($entry['images']);
								
								// For each image, if already used in blog, unset from image gallery
								for($i=0; $i<$num_images; $i++) {
									if(strpos($entry['content'], '/images/'.$image_gallery[$i]['id']) !== false || $entry['image_id'] === $image_gallery[$i]['id']) {
										unset($image_gallery[$i]);
									}
								}
								
								// If any images remain in image gallery, display them
								if(is_array($image_gallery) && !empty($image_gallery)) {
									?>
										<h3>
											<?= lang('Other images', 'イメージギャラリー', 'div'); ?>
										</h3>
										<div class="text text--outlined">
											<div class="entry__thumbnails">
												<?php
													foreach($image_gallery as $image) {
														?>
															<a class="entry__thumbnail-link" href="<?= '/images/'.$image['id'].'.'.$image['extension']; ?>" target="_blank">
																<img class="entry__thumbnail" src="<?= '/images/'.$image['id'].'.thumbnail.'.$image['extension']; ?>" />
															</a>
														<?php
													}
												?>
											</div>
										</div>
									<?php
								}
							}
						?>
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
													<a class="text text--outlined" href="/blog/<?php echo $related['friendly']; ?>/" style="align-self: flex-start; flex-grow: 1; flex-basis: 150px; margin: 0.5rem; <?php echo $related['image_id'] ? 'padding-top: calc(100px + 1rem); background-size: auto 100px; background-repeat: no-repeat; background-position: top center; background-image: url(/images/'.$related['image_id'].'.small.'.$related['extension'].'), linear-gradient(hsl(var(--background)), hsl(var(--background)));' : null; ?>">
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
		<?php

		include('../comments/partial-comments.php');
		render_default_comment_section('blog', $entry['id'], $entry['comments'], $markdown_parser);
	}
?>