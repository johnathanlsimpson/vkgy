<?php
	if(is_array($entry) && !empty($entry)) {
		$page_image = "https://vk.gy".$entry["image"];
		
		$sql_twitter = "SELECT twitter FROM users WHERE username=? LIMIT 1";
		$stmt_twitter = $pdo->prepare($sql_twitter);
		$stmt_twitter->execute([$entry["username"]]);
		$rslt_twitter = $stmt_twitter->fetchColumn();
		
		if(!empty($rslt_twitter) && preg_match("/"."^[A-z0-9_]+$"."/", $rslt_twitter)) {
			$page_creator = $rslt_twitter;
		}
		
		$page_description = preg_replace("/"."<.*?>"."/", "", strtok($entry["content"], "\n"))." (Continued…)";
		
		script([
			"/scripts/script-initDelete.js",
			"/scripts/script-addComment.js"
		]);
		
		style(["/blog/style-page-entry.css"]);
		?>
			<div class="col c1">
				<div>
					<h1>
						News
					</h1>
				</div>
				<div class="any--flex any--margin">
					<?php
						if(is_array($entry["prev_next"])) {
							foreach($entry["prev_next"] as $prev_next) {
								?>
									<div class="any--flex-grow any--no-wrap <?php echo $prev_next["type"] === "next" ? "any--align-right" : null; ?>">
										<a class="" href="/blog/<?php echo $prev_next["friendly"]; ?>/">
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
			
			<div class="col c4-AAAB">
				<div>
					<h2>
						<?php echo $entry["title"]; ?>
					</h2>
					<div class="text">
						<?php
							if($entry["image"]) {
								?>
									<div class="entry__image-container" style="background-image: url(<?php echo $entry["image"]; ?>);">
										<a class="entry__image-link" href="<?php echo $entry["image"]; ?>" target="_blank">
											<img alt="<?php echo $entry["title"]; ?>" class="entry__image" src="<?php echo $entry["image"]; ?>" />
										</a>
									</div>
								<?php
							}
						?>
						<div class="text--centered">
							<?php
								echo $entry["content"];
								
								if(is_array($entry["tags"]) && !empty($entry["tags"])) {
									foreach($entry["tags"] as $tag) {
										if($tag["friendly"] === "auto-generated") {
											echo '<hr />';
											echo '<p class="any--weaken-color symbol__error">';
											echo 'This post was automatically generated, and may be inaccurate. Any user may edit the post, until the &ldquo;auto generated&rdquo; tag is removed.';
											echo '</p>';
											break;
										}
									}
								}
							?>
						</div>
					</div>
				</div>
				
				<div>
					<h3>
						Data
					</h3>
					<div class="text text--outlined">
						<ul>
							<li>
								<h5>
									Published
								</h5>
								<a class="a--inherit" href=""><?php echo substr($entry["date_occurred"], 0, 4); ?></a>-<a class="a--inherit" href=""><?php echo substr($entry["date_occurred"], 5,2); ?></a>-<a class="a--inherit" href=""><?php echo substr($entry["date_occurred"], 8,2); ?></a>
							</li>
							<li>
								<h5>
									Author
								</h5>
								<a class="user a--inherit" href="/users/<?php echo $entry["username"]; ?>/"><?php echo $entry["username"]; ?></a>
							</li>
							<?php
								if(is_array($entry["edit_history"]) && !empty($entry["edit_history"])) {
									?>
										<li>
											<h5>
												Edit history
											</h5>
											
											<input class="obscure__input" id="obscure-edits" type="checkbox" <?php echo count($entry["edit_history"]) > 3 ? 'checked' : null; ?> >
											<ul class="ul--compact obscure__container obscure--faint">
												<?php
													foreach($entry["edit_history"] as $edit) {
														?>
															<li class="any--weaken obscure__item" style="border: none;">
																<?php echo $edit["date_occurred"]; ?>
																by
																<a class="user a--inherit" href="/users/<?php echo $edit["username"]; ?>/"><?php echo $edit["username"]; ?></a>
															</li>
														<?php
													}
												?>
												<label class="input__button obscure__button" for="obscure-edits">Show <?php echo count($entry["edit_history"]) - 3; ?> more</label>
											</ul>
										</li>
									<?php
								}
								
								if(is_array($entry["tags"]) && !empty($entry["tags"])) {
									?>
										<li>
											<h5>
												Tags
											</h5>
											<?php
												foreach($entry["tags"] as $tag) {
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
							?>
						</ul>
					</div>
					
					<?php
						if(is_array($entry["tags_artists"]) && !empty($entry["tags_artists"])) {
							?>
								<h3>
									Referenced artists
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
				</div>
			</div>
			
			<?php if($_SESSION['username'] === 'inartistic') { include('../comments/partial-add.php'); } ?>
			
			<div class="col c1">
				<div>
					<h2>
						Comments
					</h2>
					
					<div class="text">
						<form action="/php/function-add_comment.php" enctype="multipart/form-data" method="post" name="form__comment">
							<input name="item_type" type="hidden" value="blog" />
							<input name="item_id" type="hidden" value="<?php echo $entry["id"]; ?>" />
							<input data-get="null" data-get-into="value" name="comment_id" type="hidden" value="" />
							
							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Comment</label>
									<textarea class="input__textarea any--flex-grow comment__textarea" data-get="null" data-get-into="value" name="content" placeholder="your comment here..."></textarea>
								</div>
							</div>
							
							<div class="input__row any--signed-in-only">
								<div class="input__group">
									<button name="submit" type="submit">
										Submit
									</button>
									<span data-role="status"></span>
								</div>
							</div>
							
							<?php
								function comment_template($args = []) {
									ob_start();
									?>
										<li class="comment__template" data-get="thread_id" data-get-into="data-thread-id" data-thread-id="<?php $n = 0; echo $args[$n]; ?>">
											<span class="any--hidden" data-get="comment_id" data-get-into="data-comment-id" data-comment-id="<?php $n++; echo $args[$n]; ?>"></span>
											
											<div class="any--flex">
												<div class="comment__avatar-container <?php $n++; echo $args[$n]; ?> "><?php $n++; echo $args[$n]; ?></div>
												<div class="h5">
													<div data-get="date_occurred"><?php $n++; echo $args[$n]; ?></div>
													<a class="user" href="<?php $n++; echo $args[$n]; ?>"><?php $n++; echo $args[$n]; ?></a>
												</div>
												<span style="margin-left: auto;">
													<span class="any__tag symbol__trash symbol--standalone comment__delete <?php $n++; echo $args[$n]; ?>"></span>
													<span class="any__tag symbol__edit symbol--standalone comment__edit <?php $n++; echo $args[$n]; ?>"></span>
												</span>
											</div>
											
											
											<span class="any--hidden" data-get="markdown"><?php $n++; echo $args[$n]; ?></span>
											<span data-get="content"><?php $n++; echo $args[$n]; ?></span>
											
											<a class="symbol__arrow-right-circled comment__reply <?php $n++; echo $args[$n]; ?>" href="">Reply to thread</a>
										</li>
									<?php
									echo str_replace("\t", "", ob_get_clean());
								}
							?>
							
							<span class="any--hidden"><?php echo comment_template([2 => "/users/".$_SESSION["username"]."/", 3 => $_SESSION["username"]]); ?></span>
						</form>
						
						<form action="" class="any--flex any--flex-space-between any--signed-out-only comment__sign-in sign-in__container" enctype="multipart/form-data" method="post" name="sign-in__form">
							<span>Sign in to comment <span class="symbol__arrow-right-circled"></span></span>
							<div class="input__row">
								<span class="input__group">
									<input name="username" placeholder="username" size="11" />
									<input class="input--secondary" name="password" placeholder="password" size="11" type="password" />
									<span data-role="status"></span>
									<button class="" name="submit" type="submit">
										Sign in
									</button>
								</span>
							</div>
						</form>
						
						<?php
							include_once("../avatar/class-avatar.php");
							include_once("../avatar/avatar-options.php");
							include_once("../avatar/avatar-definitions.php");
							
							if(is_array($entry["comments"])) {
								foreach($entry["comments"] as $comment_thread) {
									echo '<ul class="comment__container">';
									foreach($comment_thread as $key => $comment) {
										$sql_avatar = "SELECT users_avatars.content, users.is_vip FROM users_avatars LEFT JOIN users ON users.id=users_avatars.user_id WHERE users_avatars.user_id=?";
										$stmt_avatar = $pdo->prepare($sql_avatar);
										$stmt_avatar->execute([ $comment["user"]["id"] ]);
										$rslt_avatar = $stmt_avatar->fetch();
										
										if(empty($rslt_avatar["content"])) {
											$avatar_class = "comment__no-avatar";
											$rslt_avatar["content"] = '{"head__base":"default","head__base-color":"i"}';
										}
										else {
											unset($avatar_class);
										}
										
										$avatar = new avatar($avatar_layers, $rslt_avatar["content"], ["is_vip" => $rslt_avatar["is_vip"]]);
										
										$avatar_string =
											'<svg class="comment__avatar" version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="600px" height="600px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">'.
											$avatar->get_avatar_paths().
											'</svg>';
										
										comment_template([
											($key === 0 ? "" : $comment["thread_id"]),
											$comment["id"],
											$avatar_class,
											$avatar_string,
											$comment["date_occurred"],
											"/users/".$comment["user"]["username"]."/",
											$comment["user"]["username"],
											($_SESSION["userID"] === $comment["user"]["id"] || $_SESSION["admin"] ? "" : "any--hidden"),
											($_SESSION["userID"] === $comment["user"]["id"] ? "" : "any--hidden"),
											$comment["content"],
											$markdown_parser->parse_markdown($comment["content"]),
											($key + 1 !== count($comment_thread) ? "any--hidden" : "")
										]);
									}
									echo '</ul>';
								}
							}
						?>
					</div>
				</div>
			</div>
		<?php
	}
?>

<style>
	.comment__avatar-container {
		border: 1px solid var(--background--faint);
		border-radius: 50%;
		height: 38px;
		margin-right: 1ch;
		overflow: hidden;
		width: 38px;
	}
	.comment__avatar {
		display: inline-block;
		float: left;
		height: 50px;
		margin-left: -6px;
		margin-top: -12px;
		width: 50px;
	}
	.comment__no-avatar {
		border: none;
	}
	.comment__no-avatar .head__base {
		fill: transparent;
		stroke: var(--text--faint);
		stroke-width: 3pt;
	}
	.comment__reply {
		clear: both;
	}
</style>