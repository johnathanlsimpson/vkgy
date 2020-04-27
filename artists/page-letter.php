<?php
	include('../php/function-render_component.php');
	$markdown_parser = new parse_markdown($pdo);
	
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
		"/artists/style-page-letter.css",
	]);
	
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/script-initSelectize.js",
		"/artists/script-page-letter.js",
	]);
	
	subnav([
		lang('Artist list', 'アーティスト一覧', [ 'secondary_class' => 'any--hidden' ]) => '/artists/',
		lang('Search', 'サーチ', [ 'secondary_class' => 'any--hidden' ]) => '/search/artists/',
	]);
	
	$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ-";
	
	$page_header = lang('Visual kei artist list', 'ビジュアル系アーティストの一覧', [ 'container' => 'div' ]);
?>

<div class="col c1">
	<?php
		if($error) {
			?>
				<div class="text text--outlined text--error symbol__error">
					<?php echo $error; ?>
				</div>
			<?php
		}
	?>
	
	<?php
		if($_SESSION['username'] === 'inartistic') {
			
			?>
				<div class="col c3-AAB">
					<div>
						<h2 style="display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    width: 800px;">
							<?= lang('What\'s kote kei?', 'コテ系とは', 'div'); ?>
						</h2>
						<div class="text--centered">
							<? $x = 'The term **kote kei** (コテ系), or kotekote kei (コテコテ系), comes from a Japanese onomotopeia which means “over the top” or “thickly.” It gives an impression of excess.

### What do kote kei bands look like?

Kote kei bands typically have a dark air about them, and favor bizareness, decadence, and beauty. Reality is eschewed in favor of otherworldliness. Crosses, chains, and roses may be common themes. Their costumes are excessive and impractical: vinyl, leather, buckles, feathers, lace. Black costumes with unnatural hair colors and makeup.

### What does kote kei sound like?

The music of kote kei is often speedy and agressive. It\'s likely to include distorted screams and unnatural vocal effects--and vocals which are described as “emotional” rather than “pretty.” Lyrics are often about abstract concepts or fictional tales, rather than real world scenarios.

### How did kote kei start?

Kote kei arose in the latter half othe 90s, after visual kei had solidified itself as a unique genre. Visual kei\'s formative years had seen bands naturally divide into <a href="">shiro kei</a> and <a href="">kuro kei</a>; kote kei is the natural progression of the latter. It took the tropes of kuro kei, applied the grotesque images that had been popularized by <a href="">nagoya kei</a>, and then further twisted those themes until they become their own distinct subgenre.

Like all [vkei subgenres](), kote kei is a *loose* musical and visual description of similar bands. Any vkei band can occupy several subgenres, or none.';
							echo $markdown_parser->parse_markdown($x); ?>
						</div>
					</div>
				</div>
			<?php
			
			style([
				'/tags/style-partial-tags.css',
			]);
			
			$sql_genres = 'SELECT tags_artists.*, images.extension FROM tags_artists LEFT JOIN images ON images.id=tags_artists.image_id WHERE tags_artists.type=? ORDER BY tags_artists.friendly ASC';
			$stmt_genres = $pdo->prepare($sql_genres);
			$stmt_genres->execute([ 0 ]);
			$genres = $stmt_genres->fetchAll();
			
			?>
				<svg class="any--hidden" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 228.06 61.38" style="enable-background:new 0 0 228.06 61.38;" xml:space="preserve">
					<defs>
					<path id="darrylpyon-sig" class="st0" d="M70.38,0.36c1.42,3.45-1.21,8.02-1.98,10.44c-2,6.31-3.77,13.97-5.58,20.16c-0.26,0.88,0.69,1.82,0.72,2.7
					c4.56-3.63,11.6-7.8,13.68,1.62c0.12,0.06,0.24,0.12,0.36,0.18c3.06-0.45,3.38-3.02,5.4-4.32c0.12-0.06,0.24-0.12,0.36-0.18
					c1.5-0.71,3.15,0.4,4.68,0.18c2.49-0.36,3.68-1.83,6.3-1.98c0.42,0.24,0.84,0.48,1.26,0.72c0.12,0.96,0.24,1.92,0.36,2.88
					c1.55-1.06,2.15-2.76,4.32-3.24c6.43-1.42,11.79,2.31,16.56-2.34c2.76-3.74,4.57-15.7,8.28-17.46c0.54,0,1.08,0,1.62,0
					c0.67,0.51,0.9,0.95,1.26,1.8c-0.59,5.46-7.97,14.19-4.32,19.98c3.74,0.69,6.99-1.37,10.26-0.9c0,0.06,0,0.12,0,0.18
					c-3.29,4.15-12.61,8.11-14.76,1.26c-0.12,0.06-0.24,0.12-0.36,0.18c-3.08,5.06-10.77,20.53-18.9,19.44
					c-0.24-0.36-0.48-0.72-0.72-1.08c-1.08-2.33,1.34-4.31,2.7-5.4c3.35-2.7,9.67-6.91,10.8-11.52c-3.94,0.55-7.81-1.31-11.52,0
					c-1.95,0.69-6.92,7.55-9.18,0.72c-4.04,1.69-6.16-1.01-9,3.24c-1.44,0.57-2.43,1.72-4.14,2.16c-3.41,0.88-4.42-2.43-5.94-1.8
					c-2.59,4.56-13.28,5.01-9.9-3.06c-0.42-0.12-0.84-0.24-1.26-0.36c-0.51,2.78-2.4,5.77-4.5,7.02c-4.33,2.58-12.95,1.47-14.4-2.7
					c-1.74-5.01,5.44-10.25,8.82-11.16c2.63-0.71,5.7,0.12,7.56,0.72c0-0.12,0-0.24,0-0.36c1.14-4.32,2.28-8.64,3.42-12.96
					c0.66-0.72,1.32-1.44,1.98-2.16c0.66-2.34,1.32-4.68,1.98-7.02C67.52,3.62,69,2.14,70.38,0.36z M17.28,17.46
					c8.42-0.21,16.63,8.52,9.9,16.92c-3.16,3.95-7.03,1.25-7.92,0.36c-1.34,3.08-7,8.05-10.8,5.4c-2.39-5.9,3.64-14.63,7.92-16.38
					c4.64-1.9,8.36,1.61,8.64,5.76c-0.42,0.74-0.64,0.89-1.44,1.26c-0.6-0.36-1.2-0.72-1.8-1.08c0,0.42,0,0.84,0,1.26
					c0.09,0.63,0.4,1.17,1.08,1.62c6.4-3.55,0.82-12.26-7.02-10.8C9.35,22.99,0.62,34.41,6.3,41.4c5.02,6.18,19.58-2.18,23.4-4.5
					c1.33-0.81,4.77-3.52,6.66-2.34c-3.29,6.5-25.11,20.23-33.3,9.72c-6.64-8.52,0.23-21.01,6.84-25.02
					C12.06,17.95,14.03,17.5,17.28,17.46z M161.46,21.06c4.63-0.22,5.3,3.63,3.78,7.2c0.12-0.06,0.24-0.12,0.36-0.18
					c0.47-1.41,1.12-1.51,2.34-2.16c0.42,0.12,0.84,0.24,1.26,0.36c1.13,1.56,0.7,3.07,0.54,5.4c2.04-1.65,2.33-4.98,5.58-5.4
					c1.28,0.8,1.98,1.12,1.98,3.24c1.08-0.83,3.08-2.75,4.86-2.16c1.36,0.14,2.2,0.8,2.52,1.98c1.17-0.83,3.95-0.17,6.3-0.66
					c2.5-0.52,3.17-1.64,4.86-1.32c1.32,0.9,2.37,2.07,3.96,2.7c3.88,1.23,9.71-0.53,13.5-0.72c2.88-0.14,13.22-0.85,14.58,0.18
					c-0.12,0-0.24,0-0.36,0c-11.99,3.93-27.84,7.39-32.58,2.16c-1.44,0.42-2.88,0.84-4.32,1.26c-1.32,0.06-2.64,0.12-3.96,0.18
					c-2.53,1.12-3.61,3.64-7.2,3.78c-0.87-0.54-1.24-0.91-1.44-2.16c-2.63,2.7-2.6,1.4-2.88,2.16c-1.34,3.58-7.68,20.8-14.4,21.06
					c-0.48-0.3-0.96-0.6-1.44-0.9c-2.02-7.15,7.75-16.46,10.8-20.7c-2.37,0.28-3.14-0.48-4.32-1.8c-2.34,1.62-4.68,3.24-7.02,4.86
					c-1.36,0.78-4.32,0.92-5.22,2.16c-2.3,3.89-6.94,19.19-11.16,19.62c-0.18-0.06-0.36-0.12-0.54-0.18c-0.71-0.54-1-0.95-1.08-2.16
					c2.77-7.76,5.38-15.43,8.46-22.68c-2.2,1.13-5.75,2.98-8.46,1.44c0-0.06,0-0.12,0-0.18c0-0.18,0-0.36,0-0.54
					c0.18-0.12,0.36-0.24,0.54-0.36c3.41,1.08,10.26-5.4,11.88-7.74c0.54-1.14,1.08-2.28,1.62-3.42
					C157.35,24.87,158.73,21.77,161.46,21.06z M161.46,25.2c-3.04,2.52-4.77,4.34-5.58,9C157.61,33.72,162.82,26.69,161.46,25.2z
					M18.54,27.18c-2.82,0.8-5.55,5.32-6.66,9.54c0.06,0,0.12,0,0.18,0c6.95-5.49,5-8.65,7.74-9.36C19.38,27.3,18.96,27.24,18.54,27.18z
					M53.82,31.32c-3.08,1.63-5.32,2.6-6.84,5.76c0.06,0.24,0.12,0.48,0.18,0.72c5.73,2.16,10.24-0.38,10.8-5.76
					C56.68,31.65,55.65,31.32,53.82,31.32z M71.46,33.12c-2.07,1.16-4.64,1.47-5.04,4.32c0.24,0,0.48,0,0.72,0
					C68.7,36.85,71.62,34.68,71.46,33.12z" />
					</defs>
				</svg>
				
				<div class="col c4-AAAB">
					<div class="genres__container">
						<?php
							foreach($genres as $tag) {
								?>
									<a class="text genre__container <?= 'genre--'.$tag['friendly']; ?>" href="<?= '/search/'.$item_type.'s/?tags[]='.$tag['friendly']; ?>">
										<div class="genre__image" style="background-image: url(<?= '/images/'.$tag['image_id'].'.medium.'.$tag['extension']; ?>);"></div>
										<div class="genre__name h2">
											<?= $tag['romaji'] ? lang($tag['romaji'], $tag['name'], 'div') : $tag['name']; ?>
										</div>
										<!--<svg class="genre__sig" x="0px" y="0px" viewBox="0 0 228.06 61.38"><use xlink:href="#darrylpyon-sig" /></svg>-->
									</a>
								<?php
							}
						?>
					</div>
				</div>
				
				<style>
					.genres__container {
						display: grid;
						grid-gap: 1rem;
						grid-template-columns: repeat(5, minmax(0, 1fr));
						grid-template-rows: repeat(3, 1fr);
					}
					.genre__container {
						/*background-image: url(https://vk.gy/images/36959.medium.png);*/
						height: 0;
						margin: 0;
						overflow: hidden;
						padding: 100% 100% 0 0;
						transition: background 0.2s linear;
						transform: scale(1);
						width: 100%;
					}
					.genre__container:hover .genre__image {
						transform: translateY(-10%);
					}
					.genre--kote-kei {
						/*background-image: url(https://vk.gy/images/36959.png);*/
						grid-column: 1 / 3;
						grid-row: 1 / 4;
						height: 100%;
					}
					.genre__image {
						background-position-x: 80%;
						background-position-y: 38%;
						background-repeat: no-repeat;
						background-size: 120% auto;
						bottom: -15%;
						left: 0;
						position: absolute;
						right: 0;
						top: -15%;
						transform: translateY(0%);
						transition: transform 0.2s linear;
					}
					.genre--kirakira-kei .genre__image {
						background-position-x: 60%;
						background-position-y: 17%;
					}
					.genre--shiro-kei .genre__image {
						background-position-y: 5%;
					}
					.genre--angura .genre__image {
						background-position-y: 15%;
					}
					.genre--art-kei .genre__image {
						background-position-y: 10%;
					}
					.genre--fantasy-kei .genre__image {
						background-position-y: 5%;
					}
					.genre--showa-kayou-kei .genre__image {
						background-position-x: 50%;
						background-position-y: 5%;
					}
					.genre--wafuu .genre__image {
						background-position-x: 40%;
						background-position-y: 5%;
					}
					.genre__name {
						background: radial-gradient(at bottom left, hsla(var(--background), 0.8), transparent 50%), linear-gradient(to top, hsla(var(--background), 0.9), transparent);
						bottom: 0;
						margin: 0;
						left: 0;
						line-height: 1;
						padding: 6rem 1rem 1rem 1rem !important;
						position: absolute;
						right: 0;
					}
					.genre__name :last-of-type {
						margin-top: 0.5rem; 
					}
					.genre__container:hover .genre__name {
					}
					.genre__sig {
						fill: hsl(var(--text--secondary));
						max-width: 100%;
						position: absolute;
						right: 1rem;
						bottom: 1rem;
						width: 70px;
						transform: rotate(-90deg) translate(80%, 0%);
						transform-origin: right;
					}
				</style>
			<?php
		}
	?>
	
	<div class="any--flex any--margin controls__container">
		<div class="controls__letters">
			<?php
				for($i=0; $i < strlen($str); $i++) {
					?>
						<a class="a--padded <?php echo $str[$i] === strtoupper($_GET["letter"]) ? "a--outlined" : null; ?>" href="/artists/&letter=<?php echo strtolower($str[$i]); ?>"><?php echo $str[$i] === "-" ? "#" : $str[$i]; ?></a>
					<?php
				}
			?>
		</div>
		<div class="controls__search">
				<span data-contains="artists" hidden><?php echo json_encode($full_artist_list); ?></span>
				<select class="input" id="artist_jump" placeholder="jump to artist" data-source="artists">
					<option></option>
				</select>
		</div>
	</div>
	
	<div class="col c4-AAAB">
		<ul class="text any--flex" style="flex-wrap: wrap;" data-lazyload-artists data-letter="<?php echo $str[$i]; ?>">
			<?php
				for($n=0; $n<$num_artists; $n++) {
					$artist_image_url = '/artists/'.$artist_list[$n]['friendly'].'/main.medium.jpg';
					$artist_image_exists = image_exists($artist_image_url, $pdo, true) ?: null;
					
					?>
						<li class="list__item any--flex">
							
							<a class="list__visual <?= !$artist_image_exists ? 'list--no-image' : null; ?> any--weaken" href="<?= '/artists/'.$artist_list[$n]['friendly'].'/'; ?>">
								<?php
									if($artist_image_exists) {
										?>
											<img class="list__image lazy" data-src="<?= $artist_image_url; ?>" style="height: <?= $artist_image_exists['ratio'] * 150; ?>px; width: 150px;" />
										<?php
									}
								?>
							</a>
							
							<div class="list__details any--weaken-color any--flex-grow">
								
								<div class="list__pronunciation any--weaken" style="float:right;">
									<?=
										($artist_list[$n]['pronunciation']).
										($artist_list[$n]['pronunciation'] && $artist_list[$n]['needs_hint'] ? '<br />' : null).
										($artist_list[$n]['needs_hint'] ? $artist_list[$n]['friendly'] : null);
									?>
								</div>
								<div>
									<?= lang(
										'<a href="/artists/'.$artist_list[$n]['friendly'].'/">'.($artist_list[$n]["romaji"] ?: $artist_list[$n]["name"]).($artist_list[$n]['romaji'] ? '<div class="any--weaken">'.$artist_list[$n]['name'].'</div>' : null).'</a>',
										'<a href="/artists/'.$artist_list[$n]['friendly'].'/">'.$artist_list[$n]['name'].'</a>',
										'hidden'
									); ?>
								</div>
								
								<?php
									$description_limit = 15;
									$description = $artist_list[$n]['description'];
									$description = $markdown_parser->parse_markdown($description);
									$description = strip_tags($description);
									$description = explode(' ', $description);
									$description = count($description) > 15 ? implode(' ', array_slice($description, 0, 15)).'...' : implode(' ', $description);
									
									echo '<div class="list__description">'.$description.'</div>';
									
									$tag_names = array_unique(array_filter(explode(',', $artist_list[$n]['tag_names'])));
									$tag_romajis = explode(',', $artist_list[$n]['tag_romajis']);
									$tag_friendlys = explode(',', $artist_list[$n]['tag_friendlys']);
								?>
								<div class="list__tags">
									<?php
										if(is_array($tag_names) && !empty($tag_names)) {
											foreach($tag_names as $tag_key => $tag_name) {
												?>
													<a class="list__tag a--inherit symbol__tag" href=""><?= lang($tag_romajis[$tag_key], $tag_name, 'hidden'); ?></a>
												<?php
											}
										}
									?>
								</div>
							</div>
						</li>
					<?php
				}
			?>
		</ul>
		<div>
			<?php
				$sql_new_artists = 'SELECT artists.name, artists.romaji, artists.friendly, COALESCE(artists.romaji, artists.name) AS quick_name, artists.id FROM artists_bio LEFT JOIN artists ON artists.id=artists_bio.artist_id WHERE artists_bio.type LIKE CONCAT("%", ?, "%") ORDER BY artists_bio.date_occurred DESC LIMIT 10';
				$stmt_new_artists = $pdo->prepare($sql_new_artists);
				$stmt_new_artists->execute([ '(10)' ]);
				$new_artists = $stmt_new_artists->fetchAll();
				
				if(is_array($new_artists) && !empty($new_artists)) {
					shuffle($new_artists);
					$new_artists = array_slice($new_artists, 0, 1);
				}
				
				$sql_added_artists = 'SELECT name, romaji, friendly FROM artists ORDER BY id DESC LIMIT 5';
				$stmt_added_artists = $pdo->prepare($sql_added_artists);
				$stmt_added_artists->execute();
				$recently_added_artists = $stmt_added_artists->fetchAll();
				
				$sql_artist_edits = 'SELECT artists.name, artists.romaji, artists.friendly, users.username, edits_artists.date_occurred FROM edits_artists INNER JOIN (SELECT MAX(id) AS id FROM edits_artists GROUP BY artist_id ORDER BY id DESC LIMIT 30) max_edit_artist_ids ON edits_artists.id=max_edit_artist_ids.id LEFT JOIN artists ON artists.id=edits_artists.artist_id LEFT JOIN users ON users.id=edits_artists.user_id';
				$stmt_artist_edits = $pdo->prepare($sql_artist_edits);
				$stmt_artist_edits->execute();
				$artist_edits = $stmt_artist_edits->fetchAll();
				
				$sql_video = 'SELECT artists.name, artists.romaji, artists.friendly, videos.youtube_id FROM videos LEFT JOIN artists ON artists.id=videos.artist_id ORDER BY videos.date_occurred DESC LIMIT 1';
				$stmt_video = $pdo->prepare($sql_video);
				$stmt_video->execute();
				$recent_video = $stmt_video->fetch();
				
				if(is_array($new_artists) && !empty($new_artists)) {
					?>
						<h3>
							<?= lang('New band', '新盤', 'div'); ?>
						</h3>
					<?php
					
					foreach($new_artists as $new_artist) {
						?>
							<div class="card--small"><?= $access_artist->artist_card($new_artist); ?></div>
						<?php
					}
				}
				
				if(is_array($recent_video) && !empty($recent_video)) {
					?>
						<h3>
							<div class="h5">
								<a class="artist a--inherit" href="<?= '/artists/'.$recent_video['friendly'].'/'; ?>"><?= lang($recent_video['romaji'] ?: $recent_video['name'], $recent_video['name'], 'hidden'); ?></a>
							</div>
							<?= lang('Latest video', '最近の動画', 'div'); ?>
						</h3>
						<div class="any--margin">
							<a class="youtube__embed lazy any--margin" data-id="<?= $recent_video['youtube_id']; ?>" data-src="<?= 'https://img.youtube.com/vi/'.$recent_video['youtube_id'].'/mqdefault.jpg'; ?>" href="<?= 'https://youtu.be/'.$recent_video['youtube_id']; ?>" target="_blank"></a>
						</div>
					<?php
				}
				
				if(is_array($recently_added_artists) && !empty($recently_added_artists)) {
					?>
						<h3>
							<?= lang('Recently added', '最近追加した', 'div'); ?>
						</h3>
						<ul class="text text--outlined">
							<?php
								foreach($recently_added_artists as $recent_artist) {
									?>
										<li>
											<a class="artist" href="/artists/<?= $recent_artist['friendly']; ?>/"><?= lang($recent_artist['romaji'] ?: $recent_artist['name'], $recent_artist['name'], 'hidden'); ?></a>
										</li>
									<?php
								}
							?>
						</ul>
					<?php
				}
				
				if(is_array($artist_edits) && !empty($artist_edits)) {
					?>
						<h3>
							<?= lang('Recent edits', '最近の更新', 'div'); ?>
						</h3>
						<ul class="text text--outlined">
							<?php
								foreach($artist_edits as $artist_edit) {
									?>
										<li>
											<a class="artist" href="/artists/<?= $artist_edit['friendly']; ?>/"><?= lang($artist_edit['romaji'] ?: $artist_edit['name'], $artist_edit['name'], 'hidden'); ?></a>
											<div class="h5">
												<?= $artist_edit['date_occurred'].' by <a class="user a--inherit" href="/users/'.$artist_edit['username'].'/">'.$artist_edit['username'].'</a>'; ?>
											</div>
										</li>
									<?php
								}
							?>
						</ul>
					<?php
				}
			?>
		</div>
	</div>
</div>