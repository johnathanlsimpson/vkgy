<?php
	if($is_vip) {
		script([
			"/scripts/external/script-autosize.js",
			'/scripts/external/script-tribute.js',
			'/scripts/script-initTribute.js',
			"/vip/script-page-update.js"
		]);
		
		style([
			'/style/external/style-tribute.css',
			"/vip/style-page-update.css"
		]);
		
		$page_header = 'Update VIP section';
		
		subnav([
			'Update' => '/vip/add/',
		]);
		
		?>
			<form action="/vip/function-update.php" class="col c1" enctype="multipart/form-data" method="post" name="form__update">
			
				
				<div class="col c3-AAB">
					<div>
						<h2>
							Update <a class="a--inherit" href="/vip/<?php echo $entry["friendly"]; ?>/"><?php echo $entry["title"]; ?></a>
						</h2>
						<div class="text">
							<input name="id" type="hidden" value="<?php echo $entry["id"]; ?>" />

							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Title</label>
									<input class="any--flex-grow" name="title" placeholder="title" value="<?php echo $entry["title"]; ?>" />
								</div>
							</div>

							<div class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Entry</label>
									<textarea class="autosize input__textarea any--flex-grow any--tributable" data-is-previewed="true" name="content" placeholder="your entry here"><?php echo $entry["content"]; ?></textarea>
								</div>
							</div>

							<div class="input__row">
								<div class="input__group any--flex-grow" data-role="submit-container">
									<button class="any--flex-grow" name="submit" type="submit">
										Submit
									</button>
									<span data-role="status"></span>
								</div>
								
								<div class="input__group any--flex-grow any--flex any--hidden" data-role="edit-container">
									<a class="a--padded a--outlined any--flex-grow any--align-center" data-get="url" data-get-into="href" href="">View entry</a>
									<a class="a--padded" data-get="edit_url" data-get-into="href" data-role="edit">Edit</a>
								</div>
							</div>

							<div class="text text--outlined text--notice update__result" data-role="result"></div>
						</div>
					</div>
					
					<div>
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
					</div>
				</div>
				
				<div class="col c2">
					<div>
						<?php
							$sql_prev = "SELECT friendly, title FROM vip WHERE id < ? ORDER BY id DESC LIMIT 1";
							$stmt_prev = $pdo->prepare($sql_prev);
							$stmt_prev->execute([$entry["id"]]);
							$prev = $stmt_prev->fetch();
							
							if(is_array($prev) && !empty($prev)) {
								?>
									<a href="/vip/<?php echo $prev["friendly"]; ?>/edit/">
										<span class="symbol__previous"></span>
										<?php echo $prev["title"]; ?>
									</a>
								<?php
							}
						?>
					</div>
					<div class="any--align-right">
						<?php
							$sql_next = "SELECT friendly, title FROM vip WHERE id > ? ORDER BY id ASC LIMIT 1";
							$stmt_next = $pdo->prepare($sql_next);
							$stmt_next->execute([$entry["id"]]);
							$next = $stmt_next->fetch();
							
							if(is_array($next) && !empty($next)) {
								?>
									<a class="any--align-right" href="/vip/<?php echo $next["friendly"]; ?>/edit/">
										<?php echo $next["title"]; ?>
										<span class="symbol__next"></span>
									</a>
								<?php
							}
						?>
					</div>
				</div>
			</form>
		<?php
	}
?>