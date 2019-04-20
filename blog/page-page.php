<?php
	if(is_array($entries) && !empty($entries)) {
		?>
			<div class="col c1">
				<div>
					<?php
						if(!empty($error)) {
							?>
								<div class="text text--outlined text--error">
									<?php echo $error; ?>
								</div>
							<?php
						}
					?>
					
					<h1>
						<?php
							echo $_GET["page"] === "latest" ? "Latest news" : "News, page ".sanitize($_GET["page"]);
						?>
					</h1>
				</div>
			</div>
			
			<div class="col c4-AAAB">
				<div>
					<div class="any--flex any--flex-space-between any--margin">
						<?php
							if($prev_next["prev"]["page"]) {
								?>
									<a href="/blog/page/<?php echo $prev_next["prev"]["page"]; ?>/"><span class="symbol__previous"></span> Page <?php echo $prev_next["prev"]["page"]; ?></a>
								<?php
							}
							if($prev_next["next"]["page"]) {
								?>
									<a class="any--align-right" href="/blog/page/<?php echo $prev_next["next"]["page"]; ?>/">Page <?php echo $prev_next["next"]["page"]; ?> <span class="symbol__next"></span></a>
								<?php
							}
						?>
					</div>
					
					<?php
						foreach($entries as $entry) {
							?>
								<h2>
									<a class="a--inherit" href="/blog/<?php echo $entry["friendly"]; ?>/"><?php echo $entry["title"]; ?></a>
								</h2>
								<div class="text">
									<?php
										echo $markdown_parser->parse_markdown($entry["content"]);
									?>
								</div>
							<?php
						}
					?>
					
					<div class="any--flex any--flex-space-between">
						<?php
							if($prev_next["prev"]["page"]) {
								?>
									<a href="/blog/page/<?php echo $prev_next["prev"]["page"]; ?>/"><span class="symbol__previous"></span> Page <?php echo $prev_next["prev"]["page"]; ?></a>
								<?php
							}
							if($prev_next["next"]["page"]) {
								?>
									<a class="any--align-right" href="/blog/page/<?php echo $prev_next["next"]["page"]; ?>/">Page <?php echo $prev_next["next"]["page"]; ?> <span class="symbol__next"></span></a>
								<?php
							}
						?>
					</div>
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