<?php

$access_video = new access_video($pdo);

style([
	'/blog/style-page-entry.css',
]);

// Active page
$active_page = '/blog/';

// Separate first sentence as summary, just in case we want to display it differently later
$entry['summary'] = explode("\n", $entry['content'])[0];
$entry['content'] = implode("\n", array_slice( explode("\n", $entry['content']), 1 ) );

// Check for Twitter account associated with user to set as page author
$sql_twitter = "SELECT twitter FROM users WHERE username=? LIMIT 1";
$stmt_twitter = $pdo->prepare($sql_twitter);
$stmt_twitter->execute([ $entry['user']['username'] ]);
$rslt_twitter = $stmt_twitter->fetchColumn();
$page_creator = $rslt_twitter && preg_match('/'.'^[A-z0-9_]+$'.'/', $rslt_twitter) ? $rslt_twitter : null;

// Set page description to cleaned, truncated first line of content
$page_description = substr( strip_tags( $entry['summary'] ), 0, 140 ).' (continued…)';

// If images were successfully gotten, assign them to page (assumes images are arranged by id)
if( is_array($entry['images']) && !empty($entry['images']) ) {
	
	// Set main image to page image, and also separate it into its own part of the $entry array to make later queries easier
	$entry['image'] = $entry['images'][ $entry['image_id'] ];
	$page_image = $entry['image']['url'];
	
	// If a second image was specified for social media posts, then the post's default image should actually be background image, and the social image should be page image
	if( is_numeric($entry['sns_image_id']) ) {
		$background_image = $page_image;
		$page_image = $entry['images'][ $entry['sns_image_id'] ]['url'];
	}
	
}

// Set previous/next navigation
if( $entry['prev_next'][0]['type'] === 'prev' ) {
	subnav([
		[
			'text' => $entry['prev_next'][0]['title'],
			'url' => '/blog/'.$entry['prev_next'][0]['friendly'].'/',
			'position' => 'left',
		]
	], 'directional');
}
if( isset($entry['prev_next'][1]) ) {
	subnav([
		[
			'text' => $entry['prev_next'][1]['title'],
			'url' => '/blog/'.$entry['prev_next'][1]['friendly'].'/',
			'position' => 'right',
		]
	], 'directional');
}
	
// If article is a tagged 'featured' or 'interview', the styling will change a bit, so set a flag
if( is_array($entry['tags']) && !empty($entry['tags']) ) {
	foreach( $entry['tags'] as $tag ) {
		if( $tag['friendly'] === 'interview' || $tag['friendly'] == 'feature' ) {
			$entry_is_feature = true;
			break;
		}
	}
}

// Not sure if we still needthis
/*// Make blog entries show large versions of images
$entry['content'] = str_replace('.medium.', '.large.', $entry['content']);*/
	
// Get related entries about same artist
if( is_numeric($entry['artist_id']) ) {
	$sql_related = '
		SELECT
			CONCAT( "/blog/", blog.friendly, "/" ) AS url,
			blog.title,
			blog.date_occurred,
			IF( images.id IS NOT NULL, CONCAT( "/images/", images.id, ".", images.extension ), "" ) AS image_url,
			IF( images.id IS NOT NULL, CONCAT( "/images/", images.id, ".thumbnail.", images.extension ), "" ) AS image_thumbnail_url
		FROM
			blog_artists
			LEFT JOIN blog ON blog.id=blog_artists.blog_id
			LEFT JOIN images ON images.id=blog.image_id
		WHERE
			blog.id!=?
			AND
			blog_artists.artist_id=?
			AND
			blog.is_queued=?
		ORDER BY blog.date_occurred DESC
		LIMIT 4';
	$stmt_related = $pdo->prepare($sql_related);
	$stmt_related->execute([ $entry['id'], $entry['artist_id'], 0 ]);
	$entry['related_entries'] = $stmt_related->fetchAll();
}
	
// Get user info for contributors
if( $entry['contributor_ids'] ) {
	
	$contributor_ids = json_decode($entry['contributor_ids'], true);
	
	if( is_array($contributor_ids) && !empty($contributor_ids) ) {
		foreach( $contributor_ids as $contributor_id ) {
			if( is_numeric($contributor_id) ) {
				
				$entry['contributors'][] = $access_user->access_user([ 'id' => $contributor_id, 'get' => 'name', 'limit' => 1 ]);
				
			}
		}
	}
	
}
	
// Get artist info for sidebar
if( is_numeric( $entry['artist']['id'] ) ) {
	
	// Basic info
	$entry['artist'] = $access_artist->access_artist([ 'id' => $entry['artist']['id'], 'get' => 'profile' ]);
	
	// Latest video
	$sql_video = 'SELECT videos.* FROM videos WHERE videos.artist_id=? ORDER BY videos.date_occurred DESC LIMIT 1';
	$stmt_video = $pdo->prepare($sql_video);
	$stmt_video->execute([ $entry['artist']['id'] ]);
	$entry['artist']['video'] = $stmt_video->fetch();
	
}
	
// Format sources and supplements
foreach([ 'sources', 'supplemental' ] as $supplement_type) {
	
	$supplement = $entry[ $supplement_type ];
	
	if( strlen( $supplement ) ) {
		
		// Format Twitter usernames into links
		$supplement = preg_replace('/'.'^@([A-z0-9-_]+)(?:\s|$)'.'/m', '[$1](https://twitter.com/$1)', $supplement);
		
		// Make sure is formatted as Markdown list
		$supplement = preg_replace('/'.'^'.'/m', '* ', $supplement);
		
		// Parse markdown
		$supplement = $markdown_parser->parse_markdown($supplement);
		
		// Make some changes to the way Markdown parsed elements
		$supplement = str_replace('href="https://twitter', 'class="symbol__twitter" target="_blank" href="https://twitter', $supplement);
		$supplement = str_replace('class="ul--bulleted"', '', $supplement);
		$supplement = str_replace('<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>', '', $supplement);
		
	}
	
	// Return
	$entry[ $supplement_type ] = $supplement;
	
}

// If entry has a main artist, try to get cover art of their last release to put into CDJ ad
if( strlen($entry['artist_id']) ) {
	$sql_artist_cover = 'SELECT CONCAT("/images/", images.id, ".thumbnail.", images.extension) AS thumbnail_url FROM releases LEFT JOIN images ON images.id=releases.image_id WHERE releases.artist_id=? AND releases.image_id IS NOT NULL AND images.id IS NOT NULL ORDER BY releases.date_occurred DESC LIMIT 1';
	$stmt_artist_cover = $pdo->prepare($sql_artist_cover);
	$stmt_artist_cover->execute([ $entry['artist_id'] ]);
	$rslt_artist_cover = $stmt_artist_cover->fetchColumn();
}

// Set images that appear in CDJ ad
$cdj_jackets = [
	'/images/68232.thumbnail.jpg', // KAMIJO
	'/images/62969.thumbnail.jpg', // the GazettE
	( $rslt_artist_cover ?: '/images/68564.thumbnail.jpg' ), // BabyKingdom
];

// Set error message
if( $entry['is_queued'] ) {
	$error = 'You are viewing an unpublished article.';
}

?>

<!-- Fix a bug with the minimizer -->
<style>
	@media(min-width:900px) {
		.entry__wrapper {
			--spacer: ;
		}
	}
	@media (min-width: 1300px) {
		.entry__wrapper {
			--spacer: [spacer-start] minmax(200px,20%);
		}
	}
</style>

<div class="col c1">
	
	<div class="entry__wrapper any--margin">
		
		<!-- Main article area (left) -->
		<article class="entry__article">
			
			<!-- Error message -->
			<?= $error ? '<div class="article__error text text--outlined text--error symbol__error any--margin">'.$error.'</div>' : null; ?>
			
			<!-- Image -->
			<?php if($entry['image']): ?>
				<a class="article__image <?= $entry['image']['width'] && $entry['image']['height'] / $entry['image']['width'] > 1.2 ? 'article__image--portrait' : null; ?> any--margin">
					<img alt="<?= $entry['title']; ?>" src="<?= $entry['image']['url']; ?>" height="<?= $entry['image']['height']; ?>" width="<?= $entry['image']['width']; ?>" />
				</a>
			<?php endif; ?>
			
			<!-- Date and translation -->
			<div class="article__date h5">
				<?php
					echo substr( $entry['date_occurred'], 0, 10 );
					
					if( $entry['translations'] && count($entry['translations']) > 1 ) {
						echo ' &middot; ';
						echo [ 'en' => 'English ver', 'ja' => '日本語版' ][ $entry['language'] ];
					}
				?>
			</div>
			
			<!-- Title -->
			<h1 class="article__title">
				<a href="<?= $entry['url']; ?>"><?= $entry['title']; ?></a>
			</h1>
			
			<!-- Details (author, tags) -->
			<div class="article__details data__container">
				
				<!-- Author -->
				<div class="data__item">

					<h5>
						Author
					</h5>

					<?php if( $entry['user']['avatar_url'] && file_exists( '..'.$entry['user']['avatar_url'] ) ): ?>
						<a class="article__avatar" href="<?= $entry['user']['url']; ?>">
							<img alt="<?= $entry['user']['username']; ?>" src="<?= $entry['user']['avatar_url']; ?>" />
						</a>
					<?php endif; ?>

					<?= $access_user->render_username($entry['user']); ?>

				</div>

				<!-- Contributors -->
				<?php if( is_array($entry['contributors']) && !empty($entry['contributors']) ): ?>
					<div class="data__item">

						<h5>
							<?= lang('Contributors', '寄稿家', 'hidden'); ?>
						</h5>

						<?php foreach( $entry['contributors'] as $contributor ): ?>

							<?php if( $contributor['avatar_url'] && file_exists( '..'.$contributor['avatar_url'] ) ): ?>
								<a class="article__avatar" href="<?= $contributor['url']; ?>">
									<img alt="<?= $contributor['username']; ?>" src="<?= $contributor['avatar_url']; ?>" />
								</a>
							<?php endif; ?>

							<?= $access_user->render_username($contributor); ?>

						<?php endforeach; ?>

					</div>
				<?php endif; ?>

				<!-- Translations -->
				<?php if( $entry['translations'] && count($entry['translations']) > 1 ): ?>
					<div class="data__item">

						<div class="h5">
							<?= lang('Translations', '翻訳', 'hidden'); ?>
						</div>

						<?php
							foreach($entry['translations'] as $translation) {
								if($translation['language'] != $entry['language']) {
									echo '<a class="symbol__random" href="/blog/'.$translation['friendly'].'/">';
									echo [ 'en' => 'English', 'ja' => '日本語版' ][ $translation['language'] ];
									echo '</a>';
								}
							}
						?>

					</div>
				<?php endif; ?>

				<!-- Tags -->
				<?php if( is_array($entry['tags']) && !empty($entry['tags']) ): ?>
					<div class="data__item">

						<h5>
							Tags
						</h5>

						<?php foreach($entry['tags'] as $tag): ?>
							<a class="any__tag symbol__tag" href="<?= '/blog/tag/'.$tag['friendly'].'/'; ?>"><?= $tag['name']; ?></a>
						<?php endforeach; ?>

					</div>
				<?php endif; ?>

			</div>
			
			<!-- Content -->
			<div class="article__content text text--prose any--margin">
				
				<!-- Main text -->
				<?= $entry['summary']."\n".$entry['content']; ?>
				
				<!-- Sources and other stuff -->
				<?php if( $entry['sources'] || $entry['supplemental'] ): ?>
				<div class="article__supplemental text">
					
					<?php if( $entry['supplemental'] ): ?>
						<details class="ul">
							<summary class="h2">Details</summary>
							<?= $entry['supplemental']; ?>
						</details>
					<?php endif; ?>
					
					<?php if( $entry['sources'] ): ?>
						<details class="ul">
							<summary class="h2">Sources</summary>
							<?= $entry['sources']; ?>
						</details>
					<?php endif; ?>
					
				</div>
				<?php endif; ?>
				
			</div>
			
			<!-- Sidebar -->
			<?php if( is_array($entry['artist']) && !empty($entry['artist']) ): ?>
			<aside class="article__sidebar any--margin">
				
				<h2>
					<?= lang('Profile', 'プロフィール', 'div'); ?>
				</h2>
				
				<div class="any--sticky">
					<?php
						
						// Render artist card
						$access_artist->artist_card($entry['artist']);
						
						// Description
						echo $entry['artist']['description'] ? '<div class="any--weaken any--small-margin">'.$markdown_parser->parse_markdown($entry['artist']['description']).'</div>' : null;
						
						// Musicians
						if( is_array($entry['artist']['musicians']) && !empty($entry['artist']['musicians']) ): ?>
							<ul class="any--weaken ul--compact any--small-margin">
								<?php foreach( $entry['artist']['musicians'] as $musician ): ?>
									<?php if( $musician['to_end'] ): ?>
										
										<!-- Musician -->
										<li style="line-height:1rem;">
											
											<!-- Musician SNS -->
											<div style="float:right;">
												<?php
													// Loop through artist's links and echo out musician's sns
													if(is_array($entry['artist']['urls']) && !empty($entry['artist']['urls'])) {
														foreach($entry['artist']['urls'] as $url) {
															if( $url['musician_id'] == $musician['id'] ) {
																
																// If link is SNS--let's clean this up later to use link object
																if( $url['type'] == 5 ) {
																	
																	// Only care if Twitter or Instagram--let's also clean this up later by making links store SNS platform?
																	if( strpos( $url['content'], 'twitter' ) !== false ) {
																		echo '<a style="line-height:1rem;font-size:1rem;padding:0.5rem;margin:-0.5rem -0.5rem 0 0.5rem;display:inline-block;" href="'.$url['content'].'" rel="nofollow" target="_blank"><span class="symbol--standalone symbol__twitter" style="vertical-align:middle;"></span></a>';
																	}
																	elseif( strpos( $url['content'], 'instagram' ) !== false ) {
																		echo '<a style="line-height:1rem;font-size:1rem;padding:0.5rem;margin:-0.5rem -0.5rem 0 0.5rem;display:inline-block;" href="'.$url['content'].'" rel="nofollow" target="_blank"><span class="symbol--standalone symbol__instagram" style="vertical-align:middle;"></span></a>';
																	}
																	
																}
																
															}
														}
													}
												?>
											</div>
											
											<!-- Musician position -->
											<span>
												<?= strpos( $musician['position_name'], 'support' ) !== false ? 'Sp ' : null; ?>
												<?= ['O', 'V', 'G', 'B', 'D', 'K', 'O', 'S'][ $musician['position'] ].'. '; ?>
											</span>
											
											<!-- Musician name -->
											<a class="a--inherit" href="<?= '/musicians/'.$musician['id'].'/'.$musician['friendly'].'/'; ?>">
												<?= strlen($musician['as_name']) ? ( $musician['as_romaji'] ? lang( $musician['as_romaji'], $musician['as_name'], 'parentheses' ) : $musician['as_name'] ) : null; ?>
												<?= !strlen($musician['as_name']) ? ( $musician['romaji'] ? lang( $musician['romaji'], $musician['name'], 'parentheses' ) : $musician['name'] ) : null; ?>
											</a>
											
										</li>
										
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						<?php endif;
						
						// Artist links
						if( is_array($entry['artist']['urls']) && !empty($entry['artist']['urls']) ): ?>
							<div class="any--flex">
								<?php
									foreach( $entry['artist']['urls'] as $url ) {
										if( $url['is_active'] && !is_numeric($url['musician_id']) ) {
											
											// Official site
											if( $url['type'] == 1 && !$num_official_sites ) {
												echo '<a class="a--padded a--outlined" href="'.$url['content'].'" rel="nofollow" target="_blank">'.lang('website', 'オフィシャル', 'hidden').'</a>';
												$num_official_sites++;
											}
											
											// Official shop
											elseif( $url['type'] == 2 && !$num_official_shops ) {
												echo '<a class="a--padded" href="'.$url['content'].'" rel="nofollow" target="_blank">'.lang('shop', 'ショップ', 'hidden').'</a>';
												$num_official_shops++;
											}
											
											// SNS
											elseif( $url['type'] == 5 && strpos( $url['content'], 'twitter' ) !== false ) {
												echo '<a class="a--padded" href="'.$url['content'].'" rel="nofollow" target="_blank">';
												echo '<span class="symbol--standalone symbol__twitter"></span>';
												echo '</a>';
											}
											
										}
									}
								?>
							</div>
						<?php endif;
						
						// Artist video
						if( is_array( $entry['artist']['video'] ) && !empty( $entry['artist']['video'] ) ): ?>
							<div class="article__video">
								<a class="lazy video__thumbnail" data-src="<?= 'https://img.youtube.com/vi/'.$entry['artist']['video']['youtube_id'].'/hqdefault.jpg'; ?>" href="<?= '/videos/'.$entry['artist']['video']['id'].'/'; ?>"></a>
								<a class="a--cutout any--weaken-size" href="<?= '/videos/'.$entry['artist']['video']['id'].'/'; ?>"><?= $access_video->clean_title($entry['artist']['video']['youtube_name'], $entry['artist']); ?></a>
							</div>
						<?php endif;
						
					?>
				</div>
				
			</aside>
			<?php endif; ?>
			
		</article>
		
		<!-- Promoted stories -->
		<div class="entry__supplemental any--margin">

			<!-- CDJapan -->
			<div class="entry__ad" style="flex-grow:1;">
				<a class="callout any--sticky any--margin" href="https://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/music%2Fj-pop%2Fvisualkei%2F" target="_blank">
					
					<div class="callout__image">
						<?php foreach( $cdj_jackets as $cdj_jacket ): ?>
							<img class="entry__cdjapan" src="<?= $cdj_jacket; ?>" />
						<?php endforeach; ?>
					</div>
					
					<div class="callout__text">
						Preorder vkei releases at CDJapan
					</div>
					
				</a>
			</div>
			
			<!--<div class="entry__ad" style="flex-grow:1;">
				<a class="callout any--sticky any--margin" href="" style="">
					<div class="callout__text">
						Buy rare vkei at RarezHut
					</div>
				</a>
			</div>-->
			
			<!-- Related entries -->
			<?php if( is_array($entry['related_entries']) && !empty($entry['related_entries']) ): ?>
				<div>
					
					<h3>
						<?= lang('Related', '関連ニュース', 'div'); ?>
					</h3>
					
					<ul>
						<?php foreach( $entry['related_entries'] as $related_entry ): ?>
							<li class="entry__related card__container">
								
								<a class="card__link" href="<?= $related_entry['url']; ?>"></a>
								
								<div class="related__image <?= $related_entry['image_url'] ? null : 'any--crossed-out'; ?> card--subject lazy" data-src="<?= $related_entry['image_thumbnail_url']; ?>"></div>
								
								<span class="card--subject"><?= $related_entry['title']; ?></span>
								
							</li>
						<?php endforeach; ?>
					</ul>
					
				</div>
			<?php endif; ?>
			
		</div>
		
		<!-- Comments -->
		<div class="entry__comments">
			<?php
				include('../comments/partial-comments.php');
				render_default_comment_section('blog', $entry['id'], $entry['comments'], $markdown_parser);
			?>
		</div>
		
	</div>
	
</div>