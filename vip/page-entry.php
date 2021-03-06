<?php
	if($is_vip && is_array($entry) && !empty($entry)) {
		$sql_view = "INSERT INTO vip_views (post_id, user_id) VALUES (?, ?)";
		$stmt_view = $pdo->prepare($sql_view);
		
		if($stmt_view->execute([ $entry["id"], $_SESSION["user_id"] ])) {
		}
		
		// Previous entry
		$sql_prev = "SELECT friendly, title FROM vip WHERE id < ? ORDER BY id DESC LIMIT 1";
		$stmt_prev = $pdo->prepare($sql_prev);
		$stmt_prev->execute([$entry["id"]]);
		$prev = $stmt_prev->fetch();
		if(is_array($prev) && !empty($prev)) {
			subnav([
				[
					'text' => $prev['title'],
					'url' => '/vip/'.$prev['friendly'].'/',
					'position' => 'left',
				],
			], 'directional');
		}
		
		// Next entry
		$sql_next = "SELECT friendly, title FROM vip WHERE id > ? ORDER BY id ASC LIMIT 1";
		$stmt_next = $pdo->prepare($sql_next);
		$stmt_next->execute([$entry["id"]]);
		$next = $stmt_next->fetch();
		if(is_array($next) && !empty($next)) {
			subnav([
				[
					'text' => $next['title'],
					'url' => '/vip/'.$next['friendly'].'/',
					'position' => 'right',
				],
			], 'directional');
		}
		
		?>
			<div class="col c1">
				<div>
					<div class="text--centered">
					<h2 style="<?php echo $entry["friendly"] === "development" ? "display: inline-block;" : null; ?>">
						<?php
							if($entry["friendly"] !== "development") {
								?>
									<div class="h5">
										<?php
											$username = $access_user->access_user(["id" => $entry["user_id"], "get" => "name"])["username"];
											echo $entry["date_occurred"];
										?>
										by
										<a class="user a--inherit" href="/users/<?php echo $username; ?>/"><?php echo $username; ?></a>
									</div>
								<?php
							}
							
							echo $entry["friendly"] === "development" ? "Latest development updates" : $entry["title"];
						?>
					</h2>
					</div>
					
					<?php
						if($entry["friendly"] === "development") {
							?>
								<input class="any--hidden any__partial-input" id="show-all-images" type="radio" />
								<label class="any__partial-label input__button" for="show-all-images">
									Show all
								</label>
							<?php
						}
					?>
					
					<div class="text text--centered <?php echo $entry["friendly"] === "development" ? "any__partial text--outlined" : null; ?>">
						<?php
							if($entry["friendly"] === "development") {
								$file = file("../documentation/updates.txt");
								$file = array_reverse($file);
								
								if(is_array($file) && !empty($file)) {
									?>
										<ul>
											<?php
												foreach($file as $line) {
													?>
														<li>
															<div class="h5">
																<?php echo substr($line, 0, 4)."-".substr($line, 4, 2)."-".substr($line, 6, 2); ?>
															</div>
															<?php echo substr($line, 9); ?>
														</li>
													<?php
												}
											?>
										</ul>
									<?php
								}
							}
							else {
								echo $markdown_parser->parse_markdown($entry["content"]);
							}
						?>
					</div>
				</div>
			</div>
		<?php
		
		include('../comments/partial-comments.php');
		render_default_comment_section('vip', $entry['id'], $entry['comments'], $markdown_parser);
	}
?>