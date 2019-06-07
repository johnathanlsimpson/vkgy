<?php
	if(!empty($user) && is_array($user)) {
	$page_header = lang('Member profile', 'プロフィール', ['container' => 'div']);
		
		include_once("../avatar/class-avatar.php");
		include_once("../avatar/avatar-options.php");
		include_once("../avatar/avatar-definitions.php");
		
		$access_artist = new access_artist($pdo);
		
		script([
			"/scripts/external/script-clusterize.js",
			"/account/script-page-user.js",
		]);
		
		style([
			"/account/style-page-user.css"
		]);
		
		// Wants: Get IDs
		$sql_wants = 'SELECT release_id FROM releases_wants WHERE user_id=?';
		$stmt_wants = $pdo->prepare($sql_wants);
		$stmt_wants->execute([ $user['id'] ]);
		$rslt_wants = $stmt_wants->fetchAll();
		$num_wants = count($rslt_wants);
		
		for($i=0; $i<$num_wants; $i++) {
			$wants_ids[] = $rslt_wants[$i]['release_id'];
		}
		
		// Wants: Get
		$wants = $access_release->access_release([ 'ids' => $wants_ids, 'get' => 'quick_name' ]);
		$wants = array_values($wants);
		
		// Collection: Get
		$collection = $access_release->access_release([ 'user_id' => $user['id'], 'get' => 'quick_name' ]);
		if(is_array($collection)) {
			$collection = array_values($collection);
		}
		$num_collected = is_array($collection) ? count($collection) : 0;
		
		// Collection/Wants: Count Artists
		for($i=0; $i<$num_collected; $i++) {
			$artist_ids[] = $collection[$i]["artist_id"];
		}
		$num_artists_collected = is_array($artist_ids) ? count($artist_ids) : 0;
		for($i=0; $i<$num_wants; $i++) {
			$artist_ids[] = $wants[$i]["artist_id"];
		}
		if(is_array($artist_ids)) {
			$artist_ids = array_unique($artist_ids);
			$artist_ids = array_filter($artist_ids, 'is_numeric');
			$artist_ids = array_values($artist_ids);
			
			// Collection: Get Artists
			$artists = $access_artist->access_artist([ 'id' => $artist_ids, 'get' => 'name', 'associative' => true ]);
		}
		
		// Collection/Wants: Sort
		if(is_array($collection) && !empty($collection)) {
			usort($collection, function($a, $b) use($artists) {
				return $artists[$a['artist_id']]['friendly'].$a["friendly"] <=> $artists[$b['artist_id']]['friendly'].$b["friendly"];
			});
		}
		if(is_array($wants) && !empty($wants)) {
			usort($wants, function($a, $b) use($artists) {
				return $artists[$a['artist_id']]['friendly'].$a["friendly"] <=> $artists[$b['artist_id']]['friendly'].$b["friendly"];
			});
		}
		
		// Check VIP
		if($_SESSION["loggedIn"] && is_numeric($_SESSION["userID"])) {
			$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $_SESSION["userID"] ]);
			$is_vip = $stmt_check->fetchColumn();
		}
		
		// Avatar
		$sql_avatar = "SELECT content FROM users_avatars WHERE user_id=? LIMIT 1";
		$stmt_avatar = $pdo->prepare($sql_avatar);
		$stmt_avatar->execute([ $user["id"] ]);
		$rslt_avatar = $stmt_avatar->fetchColumn();
		$avatar_class = $rslt_avatar ? null : 'user--no-avatar';
		$rslt_avatar = $rslt_avatar ?: '{"head__base":"default","head__base-color":"i"}';
		
		$avatar = new avatar($avatar_layers, $rslt_avatar, ["is_vip" => true]);
		$user["avatar"] = $avatar->get_avatar_paths();
		
		unset($avatar);
		
		// Stats: Setup
		$stats = [
			'fan_since' => ['emoji' => '🕒'],
			'member_for' => ['emoji' => '💝'],
			'comments' => ['emoji' => '💬'],
			'posts' => ['emoji' => '✍🏻'],
			'artists' => ['emoji' => '🎸'],
			'musicians' => ['emoji' => '🎤'],
			'releases' => ['emoji' => '💿'],
			'edits' => ['emoji' => '📑', 'title' => 'database edits'],
			'collection' => ['emoji' => '🎧'],
			'oldest' => ['emoji' => '⌛', 'title' => 'oldest release'],
			'newest' => ['emoji' => '⏳', 'title' => 'newest release'],
			'worth' => ['emoji' => '💸', 'title' => 'estimated worth'],
			'ratings' => ['emoji' => '📊'],
			'tagged' => ['emoji' => '🔖'],
		];
		$current_year = date('Y');
		
		// Stat: Fan since
		$stats['fan_since']['value'] = $user['fan_since'] ?: 0;
		
		// Stat: Member since
		$stats['member_for']['value'] = ($current_year - substr($user['date_added'], 0, 4));
		
		// Stat: Comments
		$sql_num_comments = 'SELECT COUNT(1) FROM comments WHERE user_id=?';
		$stmt_num_comments = $pdo->prepare($sql_num_comments);
		$stmt_num_comments->execute([ $user['id'] ]);
		$stats['comments']['value'] = $stmt_num_comments->fetchColumn();
		
		// Stat: Posts
		$sql_num_posts = 'SELECT COUNT(1) FROM blog WHERE user_id=?';
		$stmt_num_posts = $pdo->prepare($sql_num_posts);
		$stmt_num_posts->execute([ $user['id'] ]);
		$stats['posts']['value'] = $stmt_num_posts->fetchColumn();
		
		// Stat: Artists
		$sql_artists = '
			SELECT COUNT(1) AS num_added
			FROM (SELECT artist_id, MIN(date_occurred) as min_date_occurred FROM edits_artists GROUP BY artist_id) AS grouped_edits
			INNER JOIN edits_artists AS user_edits
			ON user_edits.artist_id=grouped_edits.artist_id AND user_edits.date_occurred=grouped_edits.min_date_occurred AND user_edits.user_id=?
		';
		$stmt_artists = $pdo->prepare($sql_artists);
		$stmt_artists->execute([ $user['id'] ]);
		$stats['artists']['value'] = $stmt_artists->fetchColumn();
		
		// Stat: Musicians
		$sql_musicians = '
			SELECT COUNT(1) AS num_added
			FROM
				(SELECT musician_id, MIN(date_occurred) as min_date_occurred FROM edits_musicians GROUP BY musician_id)
				AS grouped_edits
			INNER JOIN edits_musicians AS user_edits
			ON user_edits.musician_id=grouped_edits.musician_id AND user_edits.date_occurred=grouped_edits.min_date_occurred AND user_edits.user_id=?
		';
		$stmt_musicians = $pdo->prepare($sql_musicians);
		$stmt_musicians->execute([ $user['id'] ]);
		$stats['musicians']['value'] = $stmt_musicians->fetchColumn();
		
		// Stat: Releases added
		$sql_releases = '
			SELECT COUNT(1) AS num_added
			FROM
				(SELECT release_id, MIN(date_occurred) as min_date_occurred FROM edits_releases GROUP BY release_id)
				AS grouped_edits
			INNER JOIN edits_releases AS user_edits
			ON user_edits.release_id=grouped_edits.release_id AND user_edits.date_occurred=grouped_edits.min_date_occurred AND user_edits.user_id=?
		';
		$stmt_releases = $pdo->prepare($sql_releases);
		$stmt_releases->execute([ $user['id'] ]);
		$stats['releases']['value'] = $stmt_releases->fetchColumn();
		
		// Stat: Edits
		$sql_db_edits = '
		SELECT COUNT(1) AS num_edits FROM
			(
				SELECT id FROM edits_artists WHERE user_id=?
				UNION ALL
				SELECT id FROM edits_musicians WHERE user_id=?
				UNION ALL
				SELECT id FROM edits_releases WHERE user_id=?
			) AS edits
		';
		$stmt_db_edits = $pdo->prepare($sql_db_edits);
		$stmt_db_edits->execute([ $user['id'], $user['id'], $user['id'] ]);
		$stats['edits']['value'] = $stmt_db_edits->fetchColumn();
		
		// Stat: Collection
		$sql_num_collection = 'SELECT COUNT(1) FROM releases_collections WHERE user_id=?';
		$stmt_num_collection = $pdo->prepare($sql_num_collection);
		$stmt_num_collection->execute([ $user['id'] ]);
		$stats['collection']['value'] = $stmt_num_collection->fetchColumn();
		
		// Stat: Oldest Release
		$sql_oldest = 'SELECT releases.date_occurred FROM releases_collections LEFT JOIN releases ON releases.id=releases_collections.release_id WHERE releases_collections.user_id=? AND releases.date_occurred IS NOT NULL AND releases.date_occurred > "0000-00-00" ORDER BY releases.date_occurred ASC LIMIT 1';
		$stmt_oldest = $pdo->prepare($sql_oldest);
		$stmt_oldest->execute([ $user['id'] ]);
		$rslt_oldest = $stmt_oldest->fetchColumn();
		$rslt_oldest = substr($rslt_oldest, 0, 4);
		if(is_numeric($rslt_oldest)) {
			$stats['oldest']['value'] = $current_year - $rslt_oldest;
		}
		
		// Stat: Newest Release
		$sql_newest = 'SELECT releases.date_occurred FROM releases_collections LEFT JOIN releases ON releases.id=releases_collections.release_id WHERE releases_collections.user_id=? AND releases.date_occurred IS NOT NULL AND releases.date_occurred > "0000-00-00" ORDER BY releases.date_occurred DESC LIMIT 1';
		$stmt_newest = $pdo->prepare($sql_newest);
		$stmt_newest->execute([ $user['id'] ]);
		$rslt_newest = $stmt_newest->fetchColumn();
		$rslt_newest = substr($rslt_newest, 0, 4);
		if(is_numeric($rslt_newest)) {
			$stats['newest']['value'] = $rslt_newest - $current_year;
		}
		
		// Stat: Collection value
		$sql_collection_price = 'SELECT releases.price FROM releases_collections LEFT JOIN releases ON releases.id=releases_collections.release_id WHERE releases_collections.user_id=?';
		$stmt_collection_price = $pdo->prepare($sql_collection_price);
		$stmt_collection_price->execute([ $user['id'] ]);
		$rslt_collection_price = $stmt_collection_price->fetchAll();
		$num_collection_price = count($rslt_collection_price);
		
		$stats['worth']['value'] = 0;
		for($i=0; $i<$num_collection_price; $i++) {
			$tmp_price = preg_replace('/'.'(&#[A-z0-9]+?;)'.'/', '', $rslt_collection_price[$i]['price']);
			$tmp_price = preg_replace('/'.'[^0-9]'.'/', '', $tmp_price);
			$stats['worth']['value'] = $stats['worth']['value'] + (is_numeric($tmp_price) ? $tmp_price : 0);
		}
		
		// Stat: Ratings
		$sql_ratings = 'SELECT COUNT(1) FROM releases_ratings WHERE user_id=?';
		$stmt_ratings = $pdo->prepare($sql_ratings);
		$stmt_ratings->execute([ $user['id'] ]);
		$stats['ratings']['value'] = $stmt_ratings->fetchColumn();
		
		// Stat: Tags
		$sql_tags = '
		SELECT COUNT(1) AS num_tags FROM
			(
				SELECT id FROM artists_tags WHERE user_id=?
				UNION ALL
				SELECT id FROM releases_tags WHERE user_id=?
			) AS tags
		';
		$stmt_tags = $pdo->prepare($sql_tags);
		$stmt_tags->execute([ $user['id'], $user['id'] ]);
		$stats['tagged']['value'] = $stmt_tags->fetchColumn();
		
		// Stats: determine level
		$levels = [
			'default' => [
				1,
				5,
				10,
				20,
				30,
				40,
				50,
				100,
				500,
				1000
			],
			'member_for' => [
				1,
				2,
				3,
				4,
				5,
				6,
				7,
				8,
				9,
				10
			],
			'fan_since' => [
				1,
				2,
				3,
				4,
				5,
				6,
				7,
				8,
				9,
				10
			],
			'worth' => [
				100,
				500,
				1000,
				5000,
				10000,
				20000,
				50000,
				100000,
				500000,
				1000000,
			],
			'oldest' => [
				0,
				1,
				2,
				3,
				4,
				5,
				6,
				7,
				8,
				9
			],
			'newest' => [
				-9,
				-8,
				-7,
				-6,
				-5,
				-4,
				-3,
				-2,
				-1,
				0
			],
		];
		
		// Stats: Remove
		if($stats['collection']['value'] == 0) {
			unset($stats['oldest'], $stats['newest'], $stats['worth']);
		}
		
		// Stats: Overall level
		foreach($stats as $key => $stat) {
			$tmp_key = $levels[$key] ? $key : 'default';
			
			foreach($levels[$tmp_key] as $level => $min) {
				if(is_numeric($stat['value']) && $stat['value'] >= $min) {
					$stats[$key]['level'] = $level + 1;
					
					if($level + 1 === 10) {
						$level_num++;
					}
				}
			}
		}
		if($level_num < 1) {
			$level_num = ($stats['comments']['value'] || $stats['collection']['value']) ? 1 : 0;
		}
		
		// Stats: format
		foreach($stats as $key => $stat) {
			$stats[$key]['value'] = $key != 'fan_since' && $key != 'oldest' ? number_format($stat['value']) : $stat['value'];
			
			if($key === 'member_for') {
				$stats[$key]['value'] .= ' years';
			}
			elseif($key === 'worth') {
				$stats[$key]['value'] .= ' yen';
			}
			elseif($key === 'oldest') {
				$stats[$key]['value'] = $current_year - $stat['value'];
			}
			elseif($key === 'newest') {
				$stats[$key]['value'] = $stat['value'] + $current_year;
			}
			
			$stats[$key]['title'] = $stats[$key]['title'] ?: str_replace('_', ' ', $key);
		}
		
		// User links
		$sql_next = "
			(SELECT username, 'older' AS type FROM users WHERE id < ? ORDER BY id DESC LIMIT 1)
			UNION
			(SELECT username, 'prev' AS type FROM users WHERE username < ? ORDER BY username DESC LIMIT 1)
			UNION
			(SELECT username, 'rand1' AS type FROM users ORDER BY RAND() LIMIT 1)
			UNION
			(SELECT username, 'rand2' AS type FROM users ORDER BY RAND() LIMIT 1)
			UNION
			(SELECT username, 'newer' AS type FROM users WHERE id > ? ORDER BY id ASC LIMIT 1)
			UNION
			(SELECT username, 'next' AS type FROM users WHERE username > ? ORDER BY username ASC LIMIT 1)
		";
		$stmt_next = $pdo->prepare($sql_next);
		$stmt_next->execute([ $user['id'], $user['username'], $user['id'], $user['username'] ]);
		$next_user = $stmt_next->fetchAll();
		
		for($i=0; $i<count($next_user); $i++) {
			$next_users[$next_user[$i]['type']] = $next_user[$i]['username'];
		}
		
		subnav([
			[
				'text' => $next_users['older'],
				'url' => '/users/'.$next_users['older'].'/',
				'position' => 'left',
			],
			[
				'text' => $next_users['newer'],
				'url' => '/users/'.$next_users['newer'].'/',
				'position' => 'right',
			],
		], 'directional');
		
		if(strlen($next_users['rand1'])) {
			subnav([
				[
					'text' => $next_users['rand1'],
					'url' => '/users/'.$next_users['rand1'].'/',
					'position' => 'center',
				],
			], 'directional');
		}
		
		?>
			<!-- Prev Next -->
			<!--<div class="col c3 any--margin any--weaken-color">
				<div>
					<h5>
						&larr; A
					</h5>
					<?php echo $next_users['prev'] ? '<a class="user" href="/user/'.$next_users['prev'].'/">'.$next_users['prev'].'</a>' : 'N/A'; ?>
					
					<h5>
						Older
					</h5>
					<?php echo $next_users['older'] ? '<a class="a--inherit user" href="/user/'.$next_users['older'].'/">'.$next_users['older'].'</a>' : 'N/A'; ?>
				</div>
				<div style="text-align: center;">
					<h5>
						Random
					</h5>
					<?php echo $next_users['rand1'] ? '<a class="user" href="/user/'.$next_users['rand1'].'/">'.$next_users['rand1'].'</a>' : 'N/A'; ?>
				</div>
				<div style="text-align: right;">
					<h5>
						Z &rarr;
					</h5>
					<?php echo $next_users['next'] ? '<a class="user" href="/user/'.$next_users['next'].'/">'.$next_users['next'].'</a>' : 'N/A'; ?>
					
					<h5>
						Newer
					</h5>
					<?php echo $next_users['newer'] ? '<a class="user a--inherit" href="/user/'.$next_users['newer'].'/">'.$next_users['newer'].'</a>' : 'N/A'; ?>
				</div>
			</div>-->
			
			<!-- User header -->
			<div class="col c1">
				<div>
					<div class="text user__card any--flex" style="min-height: 100px; box-sizing: content-box;">
						
						<svg class="user__avatar <?php echo $avatar_class; ?>" version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="0px" height="0px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve">
							<?php echo $user["avatar"]; ?>
						</svg>
						
						<div>
							<h1 class="user__username">
								<a class="a--inherit user" href="/user/<?php echo $user["username"]; ?>/"><?php echo $user["username"]; ?></a>
							</h1>
							
							<?php
								if($user["is_admin"] > 1) {
									?>
										<span class="any__note symbol__star--full user__flair">Boss</span>
									<?php
								}
								if($user["is_admin"]) {
									?>
										<span class="any__note symbol__star--full user__flair">Editor</span>
									<?php
								}
								if($user["is_vip"]) {
									?>
										<span class="any__note symbol__star--full user__flair">VIP</span>
									<?php
								}
							?>
							
							<?php
								if($user['motto'] || $user['birthday'] || $user['gender'] || $user['website'] || $user['twitter'] || $user['tumblr'] || $user['facebook'] || $user['lastfm']) {
									?>
										<!-- User details -->
										<ul class="user__data data__container">
											<?php
												foreach(['birthday', 'gender', 'website', 'twitter', 'tumblr', 'facebook', 'lastfm'] as $field) {
													if((!empty($user[$field]) || $user[$field] === 0) && $user[$field] != '0000-00-00') {
														?>
															<li class="data__item">
																<h5>
																	<?php echo $field; ?>
																</h5>
																<?php
																	switch($field) {
																		case "member since":
																			echo substr($user["date_added"], 0, 10);
																			break;
																		case "gender":
																			echo ''.(["&hearts;", "girl", "boy"][$user[$field]]).'';
																			break;
																		case "birthday":
																			echo substr($user[$field], 0, 4).'-'.substr($user[$field], 5, 2).'-'.substr($user[$field], 8, 2);
																			break;
																		case "website":
																			echo '<a class="a--inherit" href="'.$user[$field].'">'.$user[$field].'</a>';
																			break;
																		case "twitter":
																			echo '<a class="a--inherit" href="https://twitter.com/'.$user[$field].'">@'.$user[$field].'</a>';
																			break;
																		case "tumblr":
																			echo '<a class="a--inherit" href="https://'.$user[$field].'.tumblr.com">'.$user[$field].'</a>';
																			break;
																		case "facebook":
																			echo '<a class="a--inherit" href="https://facebook.com/'.$user[$field].'">'.$user[$field].'</a>';
																			break;
																		case "lastfm":
																			echo '<a class="a--inherit" href="https://last.fm/user/'.$user[$field].'">'.$user[$field].'</a>';
																			break;
																	}
																?>
															</li>
														<?php
													}
												}
											?>
										</ul>
									<?php
									
									if($user['motto']) {
										?>
											<div class="user__motto"><?php echo $user['motto']; ?></div>
										<?php
									}
								}
								else {
									echo '<div class="user__empty symbol__error any--weaken-color">This user\'s profile is empty.</div>';
								}
							?>
						</div>
					</div>
				</div>
				
				<!-- Stats -->
				<div class="any--margin stats__wrapper">
					<h2 class="stats__title">
						<div class="any--en">
							Member statistics <sup class="any--weaken">&beta;</sup>
						</div>
						<div class="any--jp any--weaken">
							<?php echo sanitize('ランク'); ?> <sup class="any--weaken">&beta;</sup>
						</div>
					</h2>
					
					<div class="stat__level h5 level__container" data-level="<?php echo $level_num; ?>">
						<span class="level__deco"></span>
					</div>
					
					<div class="stat__container">
						<ul class="data__container">
							<?php
								if(is_array($stats) && !empty($stats)) {
									foreach($stats as $stat) {
										$data_class = $stat['value'] == 0 || $stat['value'] === 'N/A' ? 'data--hide-level' : null;
										?>
											<li class="stat__item data__item <?php echo $data_class; ?>">
												<div class="h5 level__container" data-level="<?php echo $stat['level']; ?>" data-emoji="<?php echo $stat['emoji']; ?>">
													<span class="level__deco"></span>
												</div>
												
												<h5>
													<?php echo $stat['title']; ?>
												</h5>
												
												<?php echo $stat['value']; ?>
											</li>
										<?php
									}
								}
							?>
						</ul>
					</div>
				</div>
			</div>
			
			<!-- Badges -->
			<?php if($_SESSION['username'] === 'inartistic') { include('page-badges.php'); } ?>
			
			<!-- Edit -->
			<?php
				if($_SESSION["username"] === $user["username"]) {
					?>
						<input class="obscure__input" id="obscure-edit" type="checkbox" checked />
						<div class="col c1 user__edit obscure__container obscure--height obscure--alt" id="user__edit">
							<div>
								<h1>
									Edit your account
								</h1>
							</div>
							
							<?php include("page-edit.php"); ?>
							
							<label class="input__button obscure__button" for="obscure-edit">Show options</label>
						</div>
					<?php
				}
			?>
			
			<!-- Collection -->
			<div class="col <?php echo $num_wants ? 'c3-AAB' : 'c1'; ?>">
				<div>
					<div class="any--flex" style="justify-content: space-between;">
						<h2>
							<div class="any--en">
								Collection
							</div>
							<div class="any--jp any--weaken">
								<?php echo sanitize('コレクション'); ?>
							</div>
						</h2>
						
						<div class="collection__controls">
							<label class="collection__control input__checkbox-label input__checkbox-label--selected" data-filter="" for="filter-all">all</label>
							<label class="collection__control input__checkbox-label" data-filter="" for="filter-for-sale">for sale</label>
						</div>
					</div>
					
					<div class="collection__wrapper text" id="collection-wrapper">
						<input class="any--hidden" id="filter-all" name="filter-for-sale" type="radio" value="0" />
						<input class="any--hidden" id="filter-for-sale" name="filter-for-sale" type="radio" value="1" />
						
						<ul class="any--weaken-color collection__container" id="collection-container">
							<?php
								if(is_array($collection) && !empty($collection)) {
									for($i=0; $i<$num_collected; $i++) {
										$curr_artist = $collection[$i]['artist_id'];
										$curr_letter = substr($artists[$collection[$i]['artist_id']]['friendly'], 0, 1);
										$curr_letter = is_numeric($curr_letter) ? '#' : $curr_letter;
										
										$item_class = $collection[$i]['is_for_sale'] ? 'collection--for-sale' : null;
										
										if($curr_letter != $prev_letter) {
											//ob_start();
											?>
												<li class="collection__header">
													<h4>
														<?php echo $curr_letter; ?>
													</h4>
												</li>
											<?php
											//$collection_lines[] = ob_get_clean();
										}
										
										if($curr_artist != $prev_artist) {
											//ob_start();
											?>
												<li class="collection__artist">
													<a class="artist" href="<?php echo '/artists/'.$artists[$collection[$i]['artist_id']]['friendly'].'/'; ?>"><?php echo $artists[$collection[$i]['artist_id']]['quick_name']; ?></a>
												</li>
											<?php
											//$collection_lines[] = ob_get_clean();
										}
										
										//ob_start();
										?>
											<li class="collection__item <?php echo $item_class; ?>">
												<?php
													if($_SESSION["username"] === $user["username"]) {
														?>
															<label class="collection__sell input__checkbox-label <?php echo $collection[$i]["is_for_sale"] ? "input__checkbox-label--selected symbol__checked" : "symbol__unchecked"; ?> collect" data-action="sell" data-id="<?php echo $collection[$i]["id"]; ?>">sell?</label>
														<?php
													}
												?>
												
												<a class="a--inherit" href="<?php echo '/releases/'.$artists[$collection[$i]['artist_id']]['friendly'].'/'.$collection[$i]['id'].'/'.$collection[$i]['friendly'].'/'; ?>"><?php echo $collection[$i]['quick_name']; ?></a>
												
												<?php
													if($_SESSION["username"] != $user["username"] && $collection[$i]["is_for_sale"]) {
														?>
															<span class="any__note collection__selling">for sale</span>
														<?php
													}
												?>
											</li>
										<?php
										//$collection_lines[] = ob_get_clean();
										
										$prev_artist = $curr_artist;
										$prev_letter = $curr_letter;
									}
									
									/*?>
										<script>
											var clusterData = [<?php echo json_encode($collection_lines); ?>];
										</script>
									<?php*/
								}
								else {
									?>
										<span class="symbol__error">This user hasn't collected any releases yet.</span>
									<?php
								}
							?>
						</ul>
						
						<style>
							.collection__header {
								border-top-color: var(--text--faint);
							}
							.collection__item {
								clear: both;
								padding: 0.5rem 0 0.5rem 1rem;
							}
							.collection__sell {
								float: right;
								margin: 0 0.5rem 0 auto;
							}
						</style>
					</div>
				</div>
				<div class="collection__wants <?php echo !$num_wants ? 'any--hidden' : null; ?>">
					<h3>
						<div class="any--en">
							Looking for
						</div>
						<div class="any--jp any--weaken">
							<?php echo sanitize('探してる音源'); ?>
						</div>
					</h3>
					<div class="text text--outlined">
						<ul>
							<?php
								for($i=0; $i<$num_wants; $i++) {
									?>
										<li>
											<a class="artist" href="<?php echo '/artists/'.$artists[$wants[$i]['artist_id']]['friendly'].'/'; ?>"><?php echo $artists[$wants[$i]['artist_id']]['quick_name']; ?></a>
											<a class="symbol__release" href="<?php echo '/releases/'.$artists[$wants[$i]['artist_id']]['friendly'].'/'.$wants[$i]['id'].'/'.$wants[$i]['friendly'].'/'; ?>"><?php echo $wants[$i]['quick_name']; ?></a>
										</li>
									<?php
								}
							?>
						</ul>
					</div>
				</div>
			</div>
			
			<!-- Prev Next -->
			<div class="col c3 any--margin any--weaken-color">
				<div>
					<h5>
						&larr; A
					</h5>
					<?php echo $next_users['prev'] ? '<a class="user" href="/user/'.$next_users['prev'].'/">'.$next_users['prev'].'</a>' : 'N/A'; ?>
					
					<h5>
						Older
					</h5>
					<?php echo $next_users['older'] ? '<a class="a--inherit user" href="/user/'.$next_users['older'].'/">'.$next_users['older'].'</a>' : 'N/A'; ?>
				</div>
				<div style="text-align: center;">
					<h5>
						Random
					</h5>
					<?php echo $next_users['rand2'] ? '<a class="user" href="/user/'.$next_users['rand2'].'/">'.$next_users['rand2'].'</a>' : 'N/A'; ?>
				</div>
				<div style="text-align: right;">
					<h5>
						Z &rarr;
					</h5>
					<?php echo $next_users['next'] ? '<a class="user" href="/user/'.$next_users['next'].'/">'.$next_users['next'].'</a>' : 'N/A'; ?>
					
					<h5>
						Newer
					</h5>
					<?php echo $next_users['newer'] ? '<a class="user a--inherit" href="/user/'.$next_users['newer'].'/">'.$next_users['newer'].'</a>' : 'N/A'; ?>
				</div>
			</div>
		<?php
	}
?>