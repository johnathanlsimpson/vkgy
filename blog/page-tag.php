<?php
	if(is_array($entries) && !empty($entries)) {
		?>
			<div class="col c1">
				<div>
					<h1>
						News, tagged <?php echo $tag["tag"].$artist["quick_name"]; ?>
					</h1>
				</div>
			</div>
			
			<div class="col c4-AAAB">
				<div>
					<?php
						foreach($entries as $entry) {
							$year = substr($entry["date_occurred"], 0, 4);
							
							if($year !== $prev_year) {
								?>
									<h2>
										<?php echo $year; ?>
									</h2>
								<?php
							}
							
							?>
								<div class="text text--outlined">
									<h3>
										<div class="h5">
											<?php echo $entry["date_occurred"]; ?>
										</div>
										<a class="" href="/blog/<?php echo $entry["friendly"]; ?>/"><?php echo $entry["title"]; ?></a>
									</h3>
									<?php
										echo $markdown_parser->parse_markdown($entry["content"]);
									?>
								</div>
							<?php
							
							$prev_year = $year;
						}
					?>
				</div>
				
				<div>
					<h3>
						Tags
					</h3>
					<div class="text text--outlined">
						<?php
							$sql_tags = "SELECT friendly, tag FROM tags ORDER BY friendly ASC";
							$stmt_tags = $pdo->prepare($sql_tags);
							$stmt_tags->execute();
							
							foreach($stmt_tags->fetchAll() as $tag) {
								?>
									<a class="any__tag symbol__tag" href="/blog/tag/<?php echo $tag["friendly"]; ?>/"><?php echo $tag["tag"]; ?></a>
								<?php
							}
						?>
					</div>
				</div>
			</div>
		<?php
	}
?>