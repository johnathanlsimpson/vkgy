<?php

$page_header = lang(
	$user['username'].'\''.(substr($user['username'], -1) == 's' ? null : 's').' profile',
	$user['username'].'のプロフィール',
	'div'
);

include('head-user.php');

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
if($num_wants) {
	$wants = $access_release->access_release([ 'ids' => $wants_ids, 'get' => 'quick_name' ]);
	$wants = is_array($wants) ? array_values($wants) : null;
}

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

// Stat: Fan since
$stats['fan_since']['value'] = is_numeric($user['fan_since']) ? $user['fan_since'] : substr($user['date_added'], 0, 4);
$fan_since_level_base = $stats['fan_since']['value'] - (date('Y') - $stats['fan_since']['value']);

// Stat: Member since
$stats['member_for']['value'] = ($current_year - substr($user['date_added'], 0, 4));

// Stat: Collection
$sql_num_collection = 'SELECT COUNT(1) FROM releases_collections WHERE user_id=?';
$stmt_num_collection = $pdo->prepare($sql_num_collection);
$stmt_num_collection->execute([ $user['id'] ]);
$stats['collection']['value'] = $stmt_num_collection->fetchColumn();

// Stat: Oldest Release
$sql_oldest = 'SELECT SUBSTRING(releases.date_occurred, 1, 4) FROM releases_collections LEFT JOIN releases ON releases.id=releases_collections.release_id WHERE releases_collections.user_id=? AND releases.date_occurred IS NOT NULL AND releases.date_occurred > "0000-00-00" ORDER BY releases.date_occurred ASC LIMIT 1';
$stmt_oldest = $pdo->prepare($sql_oldest);
$stmt_oldest->execute([ $user['id'] ]);
$rslt_oldest = $stmt_oldest->fetchColumn();
$stats['oldest']['value'] = $rslt_oldest && is_numeric($rslt_oldest) ? $rslt_oldest : null;

// Stat: Newest Release
$sql_newest = 'SELECT SUBSTRING(releases.date_occurred, 1, 4) FROM releases_collections LEFT JOIN releases ON releases.id=releases_collections.release_id WHERE releases_collections.user_id=? AND releases.date_occurred IS NOT NULL AND releases.date_occurred > "0000-00-00" ORDER BY releases.date_occurred DESC LIMIT 1';
$stmt_newest = $pdo->prepare($sql_newest);
$stmt_newest->execute([ $user['id'] ]);
$rslt_newest = $stmt_newest->fetchColumn();
$stats['newest']['value'] = $rslt_newest && is_numeric($rslt_newest) ? $rslt_newest : null;

// Stat: Latest Release
$sql_latest = 'SELECT date_occurred FROM releases_collections WHERE user_id=? ORDER BY date_occurred DESC LIMIT 1';
$stmt_latest = $pdo->prepare($sql_latest);
$stmt_latest->execute([ $user['id'] ]);
$rslt_latest = $stmt_latest->fetchColumn();

// Format latest release
if( $rslt_latest > date('Y-m-d', strtotime('this week')) ) {
	$stats['latest']['value'] = lang( date('l', strtotime($rslt_latest)), ['日','月','火','水','木','金','土'][date('w', strtotime($rslt_latest))].'曜日', 'hidden' );
}
else {
	$stats['latest']['value'] = substr($rslt_latest, 0, 10) ?: null;
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
		'url' => $next_users['older'] ? '/users/'.$next_users['older'].'/' : null,
		'position' => 'left',
	],
	[
		'text' => $next_users['newer'],
		'url' => $next_users['newer'] ? '/users/'.$next_users['newer'].'/' : null,
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
	
<!-- User header -->
<div class="col c3-AAB">
	<div>
		<!-- User card -->
		<?php include('partial-card.php'); ?>
		
		<?php //include('partial-badges.php'); ?>
		
		<!-- New stats -->
		<div class="any--margin">
			
			<div class="flex">
				
				<!-- Stats left -->
				<div class="level__wrapper">
					
					<!-- Current level -->
					<div class="text level__container">
						<h5 class="level__level">
							Level <span class="level__num"><?= $user_points['meta']['level']; ?></span>
						</h5>
						<span class="level__points">
							<span class="level__point-num"><?= number_format( $user_points['meta']['point_value'] ); ?></span>
							<?= lang('pt', '点', 'hidden'); ?>
						</span>
						
					</div>
					
					<!-- Next level progress -->
					<div class="text meter__container" style="--progress-percent: <?= $user_points['meta']['next_level_at'] ? min(98, max(2, $user_points['meta']['next_level_progress'])) : 100; ?>%;">
						<h5>Next level</h5>
						<div class="meter__current any--weaken-size">
							<span class="meter__spacer"></span>
							<span class="meter__current-num"><?= number_format($user_points['meta']['point_value']); ?> pt</span>
						</div>
						<div class="meter__bar"></div>
						<div class="meter__goal any--weaken "><?= $user_points['meta']['next_level_at'] ? number_format($user_points['meta']['next_level_at']) : '✨'; ?></div>
					</div>
					
				</div>
				
				<style>
					/* Stats left side */
					.level__wrapper {
						display: flex;
						flex-direction: column;
						line-height: 1;
						text-align: center;
					}
					.level__wrapper h5 {
						margin-bottom: 0.5rem;
					}
					
					/* Current level */
					.level__container {
						display: flex;
						flex-direction: column;
						line-height: 1;
						margin-bottom: 1rem;
						text-align: center;
						white-space: nowrap;
					}
					.level__level {
						color: hsl(var(--accent));
					}
					.level__num {
						font-size: 1rem;
						font-style: italic;
					}
					.level__points {
						color: hsl(var(--attention--secondary));
					}
					.level__point-num {
						font-size: 1.5rem;
						font-weight: bold;
					}
					
					/* Next level */
					.meter__container {
						--stem-height: calc(0.75rem + 3px);
						display: flex;
						flex-direction: column;
					}
					.meter__current, .meter__bar, .meter__goal {
						width: 100px;
					}
					.meter__current, .meter__goal {
						background-repeat: no-repeat;
						background-size: var(--progress-percent) var(--stem-height);
					}
					.meter__current {
						background-image: linear-gradient(to left, hsl(var(--attention--secondary)) 2px, transparent 0);
						background-position: left bottom;
						bottom: -3px;
						color: hsl(var(--attention--secondary));
						display: flex;
						font-weight: bold;
						padding-bottom: var(--stem-height);
					}
					.meter__spacer {
						margin-right: -0.5rem;
						width: var(--progress-percent);
					}
					.meter__current-num {
						white-space: nowrap;
					}
					.meter__bar {
						background: hsl(var(--background--bold));
						background-image: linear-gradient( to right, hsl(var(--attention--secondary)) var(--progress-percent), transparent 0 );
						background-repeat: no-repeat;
						border-radius: 0.25rem;
						height: 0.5rem;
						z-index: 1;
					}
					.meter__goal {
						background-image: linear-gradient(to left, hsl(var(--background--bold)) 2px, transparent 0);
						background-position: right top;
						padding-top: var(--stem-height);
						text-align: right;
						top: -3px;
					}
					.meter__goal::after {
						content: " pt";
					}
					.meter__level {
						align-self: flex-end;
					}
					
					/* Stats right side */
					.stats__container {
						margin-left: var(--gutter);
						padding: 0.5rem;
					}
					.stats__container .data__item {
						flex-basis: 30%;
					}
					@media(min-width:800px) {
						.stats__container .data__item {
							flex-basis: 150px;
						}
					}
					.stats__container [data-emoji]::after,
					.collection__stats [data-emoji]::after{
						content: attr(data-emoji);
						filter: grayscale(0);
						float: left;
						margin-right: 1ch;
					}
					
					/* Collection stats */
					.collection__stats {
						margin-bottom: 1rem;
					}
				</style>
				
				<div class="stats__container">
					<ul class="data__container">
						
						<li class="data__item" data-emoji="💬">
							<h5>
								Comments
							</h5>
							<?= number_format($user_points['added-comment']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="👍">
							<h5>
								Likes received
							</h5>
							<?= number_format($user_points['comment-liked']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="🤝">
							<h5>
								Likes given
							</h5>
							<?= number_format($user_points['liked-comment']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="✍🏻">
							<h5>
								Posts added
							</h5>
							<?= number_format($user_points['added-blog']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="🎤">
							<h5>
								Artists added
							</h5>
							<?= number_format($user_points['added-artist']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="💿">
							<h5>
								Releases added
							</h5>
							<?= number_format($user_points['added-release']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="📼">
							<h5>
								Other additions
							</h5>
							<?= number_format($user_points['added-other']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="📝">
							<h5>
								Database edits
							</h5>
							<?= number_format($user_points['edits']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="⭐">
							<h5>
								Items rated
							</h5>
							<?= number_format($user_points['rated']['num_points']) ?: 0; ?>
						</li>
						
						<li class="data__item" data-emoji="🏷️">
							<h5>
								Items tagged
							</h5>
							<?= number_format($user_points['tagged']['num_points']) ?: 0; ?>
						</li>
						
					</ul>
				</div>
				
			</div>
			
		</div>
	</div>

	<!-- User activity -->
	<div class="user__activity">
		<div class="any--margin text--outlined activity__wrapper">
			<ul class="ul--compact obscure--faint activity__container any--weaken-color">
				<?php
					$activity_limit = 15;
					$activity_offset = 0;
					$show_symbols = false;
					include('partial-activity.php');
				?>
				<div class="activity__bottom <?= is_array($rslt_activity) && count($rslt_activity) > 4 ? null : 'any--hidden'; ?> ">
					<a class="a--padded a--outlined obscure__button activity__more" href="<?= '/users/'.$user['username'].'/activity/'; ?>" style="background:hsl(var(--background--secondary));"><?= lang('All activity', '活動を表示する', 'hidden'); ?></a>
				</div>
			</ul>
		</div>
	</div>
</div>

<style>
	.activity__bottom {
		bottom: 0;
		box-shadow: inset 0 -5.5rem 2rem -4rem hsl(var(--background--secondary));
		height: 4rem;
		pointer-events: none;
		position: sticky;
		z-index: 2;
	}
	.activity__more {
		display: inline-block;
		pointer-events: auto;
	}
</style>

<!-- Collection -->
<div class="col <?php echo $num_wants ? 'c3-AAB' : 'c1'; ?>">
	<div>
		<h2 class="collection__title">
			<?php
				if($_SESSION['username'] === $user['username']) {
					echo '<a class="symbol__download collection__download" href="/users/'.$_SESSION['username'].'/&action=download" style="">CSV</a>';
					echo '<a class="symbol__download collection__download" href="/users/'.$_SESSION['username'].'/&action=download&limit=selling" style="">CSV (for sale)</a>';
				}
			?>
			<?= lang($user['username'].'\'s vkei collection', $user['username'].' V系コレクション', 'div'); ?>
		</h2>
		
		<div style="clear:both;"></div>
		
		<div class="text text--outlined collection__stats">
			<ul class="data__container">
				
				<li class="data__item" data-emoji="🎧">
					<h5>
						Items owned
					</h5>
					<?= $stats['collection']['value'] ?: 0; ?>
				</li>
				
				<li class="data__item" data-emoji="💸">
					<h5>
						Estimated worth
					</h5>
					<?= ($stats['worth']['value'] ? $stats['worth']['value'].sanitize('￥') : '?'); ?>
				</li>
				
				<li class="data__item" data-emoji="👴">
					<h5>
						Oldest item
					</h5>
					<?= $stats['oldest']['value'] ?: '?'; ?>
				</li>
				
				<li class="data__item" data-emoji="👶">
					<h5>
						Newest item
					</h5>
					<?= $stats['newest']['value'] ?: '?'; ?>
				</li>
				
				<li class="data__item" data-emoji="🆕">
					<h5>
						Last updated
					</h5>
					<?= $stats['latest']['value'] ?: '?'; ?>
				</li>
				
			</ul>
		</div>
		
		
		<input class="any--hidden" id="filter-for-sale" name="filter-for-sale" type="radio" value="1" />
		<label class="collection__control input__checkbox-label symbol__unchecked" data-filter="for-sale" for="filter-for-sale">for sale</label>
		<input class="any--hidden" id="filter-all" name="filter-for-sale" type="radio" value="0" checked />
		<label class="collection__control input__checkbox-label symbol__unchecked" data-filter="all" for="filter-all">all</label>
		<span class="collection__control symbol__filter"></span>
		
		<ul class="any--weaken-color collection__container text" id="collection-container">
			<?php
				if(is_array($collection) && !empty($collection)) {
					
					// Loop through collection and get artists/letters that are for sale
					$for_sale_artists = [];
					$for_sale_letters = [];
					for($i=0; $i<$num_collected; $i++) {
						if($collection[$i]['is_for_sale']) {
							$for_sale_artists[ $collection[$i]['artist_id'] ] = '';
							
							$for_sale_letter = substr( $artists[ $collection[$i]['artist_id'] ]['friendly'] , 0, 1 );
							$for_sale_letter = $for_sale_letter === '-' || is_numeric($for_sale_letter) ? '#' : $for_sale_letter;
							$for_sale_letters[ $for_sale_letter ] = '';
						}
					}
					$for_sale_artists = array_keys($for_sale_artists);
					$for_sale_letters = array_keys($for_sale_letters);
					
					// Loop through collection and render
					for($i=0; $i<$num_collected; $i++) {
						$curr_artist = $collection[$i]['artist_id'];
						$curr_letter = substr($artists[$collection[$i]['artist_id']]['friendly'], 0, 1);
						$curr_letter = is_numeric($curr_letter) ? '#' : $curr_letter;
						
						if($curr_letter != $prev_letter) {
							?>
								<li class="collection__header <?= in_array($curr_letter, $for_sale_letters) ? 'collection--for-sale' : null; ?>">
									<h4>
										<?= $curr_letter; ?>
									</h4>
								</li>
							<?php
						}
						
						if($curr_artist != $prev_artist) {
							?>
								<li class="collection__artist <?= in_array($curr_artist, $for_sale_artists) ? 'collection--for-sale' : null; ?>">
									<a class="artist" href="<?php echo '/artists/'.$artists[$collection[$i]['artist_id']]['friendly'].'/'; ?>"><?php echo $artists[$collection[$i]['artist_id']]['quick_name']; ?></a>
								</li>
							<?php
						}
						
						?>
							<li class="collection__item <?= $collection[$i]['is_for_sale'] ? 'collection--for-sale' : null; ?>">
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
						
						$prev_artist = $curr_artist;
						$prev_letter = $curr_letter;
					}
					
				}
				else {
					?>
						<span class="symbol__error">This user hasn't collected any releases yet.</span>
					<?php
				}
			?>
		</ul>
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