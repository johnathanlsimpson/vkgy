<?php

/* Index stuff */

// Get VIP patrons
$access_user = new access_user($pdo);
$patrons = $access_user->access_user([ 'is_vip' => true ]);

// Get non-VIP patrons
foreach([ 'redaudrey' ] as $non_vip_patron) {
	$patrons[] = $access_user->access_user([ 'username' => $non_vip_patron ]);
}

// Sort patrons
usort($patrons, function($a, $b) { return strtolower($a['username']) <=> strtolower($b['username']); });

// Make sure icons exist
$num_patrons = count($patrons);
for($i=0; $i<$num_patrons; $i++) {
	if(!file_exists('..'.$patrons[$i]['avatar_url'])) {
		$patrons[$i]['avatar_url'] = '/usericons/avatar-anonymous.png';
	}
}

// Get dummy patrons to fill gaps in layout
$patron_columns = 3;
$patron_modulo = count($patrons) % $patron_columns;
while($patron_modulo) {
	$patrons[] = [ 'avatar_url' => '/usericons/avatar-anonymous.png' ];
	$patron_modulo = count($patrons) % $patron_columns;
}

/* News */

// Get URLs
foreach($news as $news_key => $news_item) {
	$news[$news_key]['url'] = '/blog/'.$news_item['friendly'].'/';
}

/* Logic for featured cards */

// Get latest news
$latest_news = $news[0];

// Get latest interview
$latest_interviews = $access_blog->access_blog([ 'tag' => 'interview', 'get' => 'basics', 'limit' => 3 ]);

// Mark all interviews as being interview
foreach($latest_interviews as $interview_key => $latest_interview) {
	$latest_interviews[$interview_key]['is_interview'] = true;
}

// Save latest interview
$latest_interview = $latest_interviews[0];

// Get a few other news articles, skipping 0th, which will be shuffled later
for($i=1; $i<=4; $i++) {
	$latest_items[] = $news[$i];
}

// Shuffle items
shuffle($latest_items);

// Get some dates
$yesterday = date('Y-m-d', strtotime('yesterday'));
$last_month = date('Y-m-d', strtotime('1 month ago'));

// If interview in last day, that should be first no matter what
$latest_item = $latest_interview['date_occurred'] > $yesterday ? $latest_interview : $latest_news;

// Push latest item to front
array_unshift( $latest_items, $latest_item );

// Remove all but 3 items
$latest_items = array_slice( $latest_items, 0, 3 );

// If interview older than yesterday, push an interview to the end
if($latest_interview['date_occurred'] < $yesterday) {
	
	// If latest interview older than one month, push random interview
	if($latest_interview['date_occurred'] < $last_month) {
		shuffle($latest_interviews);
	}
	
	// Push
	$latest_items[] = $latest_interviews[0];
	
}

// Loop through items and get URL and image URLs
foreach($latest_items as $item_key => $latest_item) {
	
	// URL
	$latest_items[$item_key]['url'] = '/blog/'.$latest_item['friendly'].'/';
	
	// Decide pill
	if($latest_item['is_interview']) {
		$pill = 'interview';
	}
	else {
		if( $item_key == 0 && $latest_item['date_occurred'] > date('Y-m-d', strtotime('1 day ago')) ) {
			$pill = 'breaking';
		}
		else {
			$pill = 'news';
		}
	}
	$latest_items[$item_key]['pill'] = $pill;
	
	// Image
	$image = $latest_item['image'];
	
	// Some URLs are given as thumbnail
	$url = str_replace('.thumbnail.', '.', $image['url']);
	$medium_url = str_replace('.', '.medium.', $url);
	$thumbnail_url = str_replace('.', '.thumbnail.', $url);
	
	// Push
	$latest_items[$item_key]['image'] = array_merge( $latest_items[$item_key]['image'], [
		'url' => $url,
		'medium_url' => $medium_url,
		'thumbnail_url' => $thumbnail_url
	] );
	
}



/* Page title */
$page_header = lang('Welcome to vkgy', 'vkgyへようこそ', 'hidden');

/* Intro */
ob_start();
?>
	<!-- Introductory paragraph -->
	<div class="col c2">
		
		<!-- Intro paragraph -->
		<div class="home-head__text any--margin">
			
			<div class="home-head__title">
				<?= lang('Welcome to vkgy,', 'vkgyへようこそ―', 'hidden'); ?>
			</div>
			<p class="home-head__p">
				<?= lang(
					'the visual kei library. Track your collection, interact with fans, learn hidden history, and get the latest news and interviews.',
					'vkgyはビジュアル系のライブラリです。ここでは、コレクションを追跡されます、バンドの歴史を記録する、と最新のニュースを集まる。',
					'hidden' ); ?>
			</p>
			<div class="home-head__more">
				<a class="symbol__arrow-right-circled" href=""><?= lang('About vkgy', 'vkgyとは', 'hidden'); ?></a>
				<a class="symbol__arrow-right-circled" href=""><?= lang('What\'s vkei?', 'ビジュアル系とは', 'hidden'); ?></a>
			</div>
			
		</div>
		
		<!-- Intro Patreon link -->
		<div class="home-head__support any--margin">
			
			<a class="home-head__patreon symbol__patreon" href="https://patreon.com/vkgy" target="_blank">Support Us</a>
			<span class="home-head__supported">Thank you to our kind Patrons, who make vkgy possible.</span>
			
		</div>
		
	</div>
	
	<!-- Featured cards -->
	<div class="col c1">
		<div class="intro__cards">
			
			<?php foreach($latest_items as $item_key => $latest_item): ?>
				<a class="intro__card text any--flex" href="<?= $latest_item['url']; ?>" style="background-image:url(<?= $latest_item['image']['thumbnail_url']; ?>);">
					
					<span class="intro__card-image lazy" data-src="<?= $latest_item['image']['medium_url']; ?>"></span>
					
					<div class="intro__card-title h2">
						<span class="intro__card-pill h5"><?= $latest_item['pill']; ?></span>
						<div class="intro__card-text"><?= $latest_item['title']; ?></div>
					</div>
					
				</a>
			<?php endforeach; ?>
			
		</div>
	</div>
<?php $GLOBALS['page_header_supplement'] = ob_get_clean(); 

	
	script([
		'/scripts/script-signIn.js',
	]);

$background_image = null;
?>


<style>
	.intro__cards {
		--num-columns: 1;
		display: grid;
		grid-gap: 1rem;
		grid-template-columns: repeat(var(--num-columns), minmax(0, 1fr));
	}
	@media(min-width:500px) {
		.intro__cards {
			--num-columns: 2;
		}
	}
	@media(min-width:700px) {
		.intro__cards {
			--num-columns: 3;
		}
	}
	@media(min-width:1000px) {
		.intro__cards {
			--num-columns: 4;
		}
	}
	.intro__card {
		--padding-top: 150px;
		align-items: stretch;
		background-color: transparent;
		background-position: center;
		background-size: 0 0;
		border: none;
		border-radius: 3px;
		flex-direction: column;
		justify-content: flex-end;
		margin: 0;
		overflow: hidden;
		padding-top: var(--padding-top);
	}
	@media(min-width:1000px) {
		.intro__card {
			--padding-top: 200px;
		}
	}
	@media(max-width:499.99px) {
		.intro__card:nth-of-type(4) {
			display: none;
		}
	}
	@media(max-width:699.99px) {
		.intro__card:nth-of-type(2),
		.intro__card:nth-of-type(3) {
			display: none;
		}
	}
	@media(max-width:999.99px) {
		.intro__card:nth-of-type(3) {
			display: none;
		}
	}
	.intro__card:hover {
		color: white;
	}
	.intro__card::before {
		background: inherit;
		background-color: hsl(var(--accent));
		background-size: cover;
		bottom: -1rem;
		content: "";
		left: -1rem;
		position: absolute;
		right: -1rem;
		top: -1rem;
		filter: blur(5px);
		z-index: -1;
	}
	.intro__card-image {
		background-position: center;
		background-size: cover;
		bottom: 0;
		left: 0;
		position: absolute;
		right: 0;
		top: 0;
		transform: scale(1);
		transition: transform 0.2s ease-in-out;
		z-index: -1;
	}
	.intro__card:hover .intro__card-image {
		transform: scale(1.1);
	}
	.intro__card-title {
		line-height: 1.25em;
		margin: 0;
		padding: 0;
	}
	.intro__card-text {
		max-height: calc(3 * 1.25em);
		overflow: hidden;
	}
	.intro__card-title::after {
		--shadow-color: var(--accent);
		background: none;
		background-image: linear-gradient(4deg, hsla(var(--shadow-color), 0.95) 30%, hsla(var(--shadow-color), 0.0) 70%);
		border: none;
		bottom: -1rem;
		content: "";
		display: block;
		height: auto;
		left: -1rem;
		position: absolute;
		right: -1rem;
		top: -10rem;
		width: auto;
		z-index: -1;
	}
	.intro__card:hover .intro__card-title::after {
		--shadow-color: var(--attention--secondary);
	}
	.intro__card-pill {
		background: white;
		border-radius: 3px;
		color: hsl(var(--accent));
		padding: 0 3px 0 4px;
	}
</style>
<style>
	/* Hide regular header */
	.header__header > h1 {
		display: none;
	}
	
	.home-head__title {
		font-size: 2rem;
		font-weight: bold;
		margin-bottom: 0.5rem;
	}
	.home-head__p {
		font-size: 1.5rem;
	}
	.home-head__more {
		color: hsl(var(--accent));
		display: none;
		font-weight: bold;
		margin-top: 0.5rem;
		font-size: 1.5rem;
	}
	
	@media(min-width:800px) {
		.home-head__support {
			text-align: right;
		}
		.home-head__supported {
			margin-left: auto;
			width: 200px;
		}
	}
	.home-head__patreon {
		background: #f96854;
		color: white;
		display: inline-block;
		font-weight: bold;
		margin-bottom: 0.5rem;
		padding: 0.5rem;
	}
	.home-head__supported {
		color: white;
		display: block;
		font-size: 1rem;
	}
</style>


<div class="col c4-ABBC section__main">
	<div class="main__left">
		
		<a class="browse__link browse--artists text" href="/artists/">
			<div class="h2 browse__title">
				<?= lang('browse artists', 'アーティスト一覧', 'div'); ?>
			</div>
		</a>
		<span class="any--weaken" style="display:block;height:0;transform:translateY(-3rem);">art by <a class="a--inherit" href="https://www.instagram.com/darrylpyon/" target="_blank">@darrylpyon</a></span>
		
		<a class="browse__link browse--releases text" href="/releases/">
			<div class="h2 browse__title">
				<?= lang('release calendar', 'リリースカレンダー', 'div'); ?>
			</div>
		</a>
		
		<style>
			.browse__link {
				border: none;
				color: white;
				display: block;
				padding-top: 50%;
				overflow: visible;
			}
			.browse__link .any--weaken {
				color: inherit;
			}
			.browse__link:hover {
				color: white;
			}
			.browse--artists::before,
			.browse--artists::after {
				background-position: top 0 left -150%;
				background-repeat: no-repeat;
				background-size: 90% auto;
				border-radius: inherit;
				bottom: 0;
				content: "";
				left: 0;
				position: absolute;
				right: 0;
				top: -2.5rem;
				z-index: 0;
			}
			.browse--artists::after {
				background-image: url(/main/main-chibi-02.png);
				background-position: top 10% right -200%;
			}
			.browse--artists::before {
				background-image: url(/main/main-chibi-01.png);
			}
			.browse--releases {
				background-image: url(/main/main-cds-02.jpg);
				background-position: center;
				background-size: cover;
			}
			.browse__title {
				border-radius: inherit;
				color: inherit;
				margin: 0;
				padding: 0;
				z-index: 1;
			}
			.browse__title::after {
				--shadow-color: var(--accent);
				background: none;
				background-image: linear-gradient(4deg, hsla(var(--accent), 0.95) 10%, hsla(var(--accent), 0.0) 80%);
				background-image: linear-gradient(4deg, hsla(var(--shadow-color), 0.95) 30%, hsla(var(--shadow-color), 0.0) 80%);
				border: none;
				border-radius: inherit;
				bottom: -1rem;
				content: "";
				display: block;
				height: auto;
				left: -1rem;
				position: absolute;
				right: -1rem;
				top: -3rem;
				width: auto;
				z-index: -1;
			}
			.browse__link:hover .browse__title::after {
				--shadow-color: var(--attention--secondary);
			}
		</style>
		
		<div class="main__ranking">
			<h3>
				<div class="h5">
					<?= date('n/j', strtotime("-2 weeks sunday", time())).'~'.date('n/j', strtotime("-1 weeks sunday", time())); ?>
				</div>
				<?= tr('Band access ranking', ['ja'=>'アクセスランキング','lang'=>true,'lang_args'=>'div']); ?>
			</h3>
			<ol class="text text--outlined text--compact ul--compact">
				<?php
					if(is_array($rslt_rank) && !empty($rslt_rank)) {
						foreach($rslt_rank as $rank_num => $artist) {
							?>
								<li class="ranking__item">
									<span class="ranking__number symbol__user"></span>
									<a class="artist artist--no-symbol" href="<?= '/artists/'.$artist['friendly'].'/'; ?>"><?= lang($artist['quick_name'], $artist['name'], 'hidden'); ?></a>
									<span class="any--weaken"><?= '+'.$artist['num_difference']; ?></span>
								</li>
							<?php
						}
					}
				?>
			</ol>
		</div>
		
		<div class="main__aod">
			<h3>
				<?= tr('Artist of the day', [ 'ja' => '今日のアーティスト', 'lang' => true, 'lang_args' => 'div' ]); ?>
			</h3>
			<div class="card--small">
				<?php $access_artist->artist_card($artist_of_day); ?>
			</div>
		</div>
		
		<?php /*
		<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- home - left side -->
		<ins class="adsbygoogle"
							style="display:block"
							data-ad-client="ca-pub-5797371558296978"
							data-ad-slot="5986742160"
							data-ad-format="auto"
							data-full-width-responsive="true"></ins>
		<script>
							(adsbygoogle = window.adsbygoogle || []).push({});
		</script> */ ?>
	
	<style>
		@media(min-width:800px) and (max-width: 1199.99px) {
			body {
			}
			.main__middle {
				flex-direction: column;
			}
			.main__middle > * {
				width: 100%;
			}
		}
		@media(min-width:1200px) and (max-width: 1399.99px) {
			.news__item2 {
				flex-direction: column;
			}
			.news__image {
				margin: 0 0 1rem 0;
			}
		}
		@media(min-width:800px) and (max-width: 899.99px) {
			.news__item2 {
				flex-direction: column;
			}
			.news__image {
				margin: 0 0 1rem 0;
			}
		}
	</style>
	</div>
	
	<div class="main__middle col c2">
		
		<div class="main__middle-left">
			<!-- News -->
			<ul class="news__container2 text">
				<?php
					foreach($news as $news_item) {
						?>
							<li class="news__item2 any--flex">

								<a class="news__image lazy" href="<?= $news_item['url']; ?>" data-src="<?= $news_item['image']['url']; ?>"></a>

								<div class="news__text">
									<div class="news__supertitle h5">
										<?= $news_item['date_occurred']; ?>
										<a class="user a--inherit" data-icon="<?= $news_item['user']['icon']; ?>" data-is-vip="<?= $news_item['user']['is_vip']; ?>" href="<?= $news_item['user']['url']; ?>"><?= $news_item['user']['username']; ?></a>
									</div>

									<a class="news__title2 h2" href="<?= $news_item['url']; ?>"><?= $news_item['title']; ?></a>
								</div>

							</li>
						<?php
					}
				?>
				<style>
					.news__container2 {
					}
					.news__item2 {
						justify-content:flex-start;
					}
					.news__supertitle {
					}
					.news__image {
						background-position: 30% center;
						background-size: cover;
						flex: none;
						display: block;
						height: 80px;
						width: 150px;
						margin-right: 1rem;
					}
					.news__image:hover {
						opacity: 0.75;
					}
				</style>
			</ul>
			
			<div class="text text--outlined h5 any--margin" style="margin-top:-3rem;">
				<a class="a--inherit" href="/blog/">All news</a> / 
				<a class="a--inherit" href="/interview/">Interviews</a> / 
				<a class="a--inherit" href="/blog/tag/new-band/">New bands</a> / 
				<a class="a--inherit" href="/blog/tag/disbandment-revival/">Disbandments</a>
			</div>
		</div>
		
		<div class="main__middle-right">
		
			<style>
				.comments__container2 {
					align-self: stretch;
				}
				.comment__avatar-container {
					margin-top: 0.25rem;
					overflow: visible;
				}
				.comment__avatar-container:hover {
					background: hsl(var(--interactive));
				}
				.comment__next {
					display: none;
				}
				.comment__content {
					background: hsl(var(--background));
					border-radius: 5px;
					line-height: 1;
					margin-top: 0.5rem;
					padding: 0.5rem;
				}
				.comment__item::before {
					background: linear-gradient(to bottom left, hsl(var(--background)) 50%, transparent calc(50% + 1px));
					bottom: 1.75rem;
					content: "";
					display: block;
					height: 0.75rem;
					left: 2.25rem;
					position: absolute;
					width: 0.75rem;
				}
				.comment__item:last-of-type::before {
					bottom: 0.75rem;
				}
				.comment__user {
					margin-right: 1ch;
				}
			</style>

			<!-- Comments -->
			<ul class="comments__container2 any--margin">
				<?php
					$comments = array_slice($comments, 0, 10);
					for($i=0; $i<count($comments); $i++) {

						$comment_class = null;
						$comments[$i]['user']['avatar_url'] = '/usericons/avatar-'.(file_exists('../usericons/avatar-'.$comments[$i]['user']['username'].'.png') ? $comments[$i]['user']['username'] : 'anonymous').'.png?'.date('YmdH');

						if(!$comments[$i]['is_approved']) {
							$comment_class .= ($_SESSION['can_approve_data'] ? 'comment--unapproved' : 'any--hidden');
						}

						?>
							<li class="comment__item <?= $comment_class; ?>">
								<div class="any--flex">

									<a class="comment__avatar-container" data-icon="<?= $comments[$i]['user']['icon']; ?>" data-is-vip="<?= $comments[$i]['user']['is_vip']; ?>" href="<?= $comments[$i]['user']['url']; ?>">
										<img alt="<?= $comments[$i]['user']['username']; ?>'s avatar" class="comment__avatar lazy" data-src="<?= $comments[$i]['user']['avatar_url']; ?>" />
									</a>

									<div class="comment__comment">
										<h5 class="any--flex">
											<a class="user a--inherit comment__user" data-icon="<?= $comments[$i]['user']['icon']; ?>" data-is-vip="<?= $comments[$i]['user']['is_vip']; ?>" href="<?= $comments[$i]['user']['url']; ?>"><?= $comments[$i]['user']['username']; ?></a>
											<?php echo substr($comments[$i]["date_occurred"], 5); ?>
										</h5>

										<div class="any--flex">
											<a class="comment__content" href="<?= $comments[$i]['url'] ? $comments[$i]['url'].'#comments' : '/comments/#comment-'.$comments[$i]['id']; ?>"><?= strip_tags($comments[$i]["content"]); ?></a>
											<a class="comment__next symbol__next" href="<?php echo $comments[$i]['url'] ? $comments[$i]['url'].'#comments' : '/comments/#comment-'.$comments[$i]['id']; ?>">Read</a>
										</div>

										<span class="any--hidden symbol__error comment__notice">This comment is awaiting approval.</span>
									</div>
								</div>
							</li>
						<?php
					}
				?>
			</ul>
			
		</div>
		
		<div class="support__container any--margin" style="width:100%;">
			<?php
				$ads = [
					'<a class="support__link" href="http://rarezhut.net/" target="_blank"><img alt="RarezHut" class="support__image lazy" data-src="/main/ad-rarezhut-wide.png" /></a>',
					'<a class="support__link" href="https://discord.gg/jw8jzXn" target="_blank"><img alt="vkgy at Discord" class="support__image lazy" data-src="/main/ad-discord.png" /></a>',
					'<a class="support__link" href="http://witchthrone.com/" target="_blank"><img alt="Witchthrone" class="support__image lazy" data-src="/main/ad-witchthrone-wide.gif" /></a>',
					'<a class="support__link" href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/" target="_blank"><img alt="Buy vk merch at CDJapan" class="support__image lazy" data-src="/main/ad-cdjapan-wide.jpg" /></a>',
					'<a class="support__link" href="https://www.patreon.com/vkgy" target="_blank"><img alt="Support vkgy at Patreon" class="support__image lazy" data-src="/main/ad-patreon.png" /></a>',
				];

				shuffle($ads);

				foreach($ads as $ad) {
					echo $ad.' ';
				}
			?>
		</div>
	</div>

	<div class="main__right">
		<div class="main__updates">
			<h3>
				<?php echo lang('Database updates', '最近の更新', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h3>

			<input class="obscure__input" id="obscure-updates" type="checkbox" checked />
			<div class="text text--outlined obscure__container obscure--faint obscure--height">
				<ul style="z-index: 1;">
					<?php
						for($i=0; $i<$num_updates; $i++) {
							?>
								<li>
									<h5>
										<?php echo substr($updates[$i]["date_edited"], 0, 10); ?>
									</h5>

									<a class="symbol__<?php echo $updates[$i]["type"]; ?> <?php echo $updates[$i]["type"]; ?>" href="<?php echo $updates[$i]["url"]; ?>"><?php echo $updates[$i]["quick_name"]; ?></a>

									<?php
										if($updates[$i]["type"] === "release" && $updates[$i]["artist_quick_name"]) {
											?>
												<div class="any--weaken">
													<a class="artist" href="<?php echo $updates[$i]["artist_url"]; ?>"><?php echo $updates[$i]["artist_quick_name"]; ?></a>
												</div>
											<?php
										}

										if($updates[$i]["type"] === "news" && $updates[$i]["artist_quick_name"]) {
											?>
												<div class="any--weaken">
													<a class="" href="<?php echo $updates[$i]["url"]; ?>">&ldquo;<?php echo $updates[$i]["artist_quick_name"]; ?>&rdquo;</a>
												</div>
											<?php
										}
									?>
								</li>
							<?php
						}
					?>
				</ul>
				<label class="input__button obscure__button" for="obscure-updates">Show more</label>
			</div>
		</div>
	</div>
</div>

<!-- Background color -->
<div class="patreon__bg">
	<!-- Background slashes at top and bottom -->
	<div class="patreon__diagonals col c1 any--flex">
		<!-- Spacing helper to keep all content in center -->
		<div class="patreon__spacing col c4-ABBC">
			<!-- Empty space on side -->
			<div class="patreon__empty"></div>
			
			<!-- Patreon content starts here -->
			<div class="patreon__container col c3-AAB any--flex">
				
				<!-- Text on left side -->
				<div class="patreon__text">
					
					<h1 class="patreon__title">
						<?= lang('Thank you for supporting vkgy', 'サポーターの皆様のおかげです', 'div'); ?>
					</h1>
					
					<p class="patreon__p">
						vkgy is possible thanks to our <a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">Patreon</a> supporters!<br />Please consider joining them, for these benefits:
					</p>
					
					<ul class="patreon__list">
						<li>No ads</li>
						<li><span class="patreon__badge" >VIP</span> badge</li>
						<li>Early access to features</li>
						<li>Priority support</li>
						<li>Full-resolution images</li>
						<li>Exclusive Discord channel</li>
						<li>Dev blog</li>
						<li>Avatar items and colors</li>
						<li><a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">+ more</a></li>
					</ul>
					
					<a class="patreon__button a--inherit" href="https://patreon.com/vkgy" target="_blank">
						<span class=""></span>
						Become a Patron
					</a>
					
				</div>
				
				<!-- Avatar wall on right side -->
				<div class="patreon__wall">
					<!-- Handles scrolling animation -->
					<div class="patreon__scroll">
						<?php
							// Display all avatars twice to give us room to repeat scroll
							for($i=0; $i<2; $i++) {
								foreach($patrons as $patron) {
									if($patron['username']) {
										?>
											<a class="patreon__patron" href="<?= $patron['url']; ?>">
												<img alt="<?= $patron['username']; ?>" class="patreon__avatar" src="<?= $patron['avatar_url']; ?>" />
												<span class="user patreon__username" data-icon="<?= $patron['icon']; ?>"><?= $patron['username']; ?></span>
											</a>
										<?php
									}
									else {
										?>
											<img class="patreon__avatar" src="<?= $patron['avatar_url']; ?>" />
										<?php
									}
								}
							}
						?>
					</div>
				</div>
				
			</div>
			<!-- End Patreon content -->
			
			<!-- Empty space on side -->
			<div class="patreon__empty"></div>
		</div>
	</div>
</div>

<style>
	.patreon__bg {
		background: hsla(var(--vkgy-red), 0.4);
		color: white;
		text-align: center;
	}
	.patreon__diagonals::before {
		--slash-direction: to bottom left;
		--slash-bottom: auto;
		--slash-top: 0;
	}
	.patreon__diagonals::after {
		--slash-direction: to top right;
		--slash-bottom: 0;
		--slash-top: auto;
	}
	.patreon__diagonals::before,
	.patreon__diagonals::after {
		background-image: linear-gradient(var(--slash-direction), hsla(var(--background--secondary), 1) 50%, hsla(var(--background--secondary), 0) calc(50% + 1px));
		background-position: left var(--negative-gutter) var(--slash-position) -1px;
		background-repeat: no-repeat;
		background-size: calc(100vw + var(--gutter) * 2) 3rem;
		bottom: var(--slash-bottom);
		content: "";
		display: block;
		height: 3rem;
		left: var(--negative-gutter);
		position: absolute;
		right: var(--negative-gutter);
		top: var(--slash-top);
		z-index: 2;
	}
	.patreon__container {
		flex-direction: row;
	}
	.patreon__container a:hover {
		color: hsl(146,65%,25%);
	}
	.patreon__container .a--inherit:hover {
		background-image: linear-gradient(hsl(146,65%,25%),hsl(146,65%,25%));
	}
	
	.patreon__text {
		padding-bottom: 6rem;
		padding-top: 6rem;
	}
	.patreon__title {
		background: hsl(var(--vkgy-red));
		display: inline-block;
		color: white;
		font-size: 2rem;
		margin: 0 auto 3rem auto;
		padding: 0 1rem;
		transform: rotate(-3deg);
	}
	@media(max-width:699.99px) {
		.patreon__title {
			font-size: 1.5rem;
		}
	}
	.patreon__title .any--weaken {
		color: hsl(0 0% 80%);
	}
	.patreon__p {
		color: hsl(var(--accent));
		font-size: 1.5rem;
		font-weight: bold;
		line-height: 1.5;
		margin-bottom: 3rem;
	}
	.patreon__list {
		color: white;
		display: flex;
		flex-wrap: wrap;
		font-size: 1.25rem;
		list-style-type: none;
		margin-bottom: -1rem;
	}
	.patreon__list li {
		border: none;
		flex-grow: 1;
		margin: 0 0 2rem 0 !important;
		padding: 0 1rem !important;
		width: 33.3%;
	}
	.patreon__badge {
		border-radius: 3px;
		box-shadow: inset 0 0 0 1px;
		padding: 0 5px;
	}
	.patreon__button {
		background: #f96854;
		display: inline-block;
		font-size: 1.5rem;
		font-weight: bold;
		line-height: 1;
		margin: 3rem auto 0 auto;
		padding: 1rem;
		transform: rotate(-0deg);
	}
	@media(max-width:699.99px) {
		.patreon__button {
			font-size: 1.5rem;
		}
	}
	.patreon__button:hover {
		color: white !important;
	}
	
	.patreon__wall {
		align-self: stretch;
		min-height: 500px;
		-webkit-mask-image: linear-gradient(transparent, black 3rem, black calc(100% - 3rem), transparent);
		mask-image: linear-gradient(transparent, black 3rem, black calc(100% - 3rem), transparent);
		order: 1;
	}
	.patreon__scroll {
		display: grid;
		grid-gap: 1rem;
		grid-template-columns: repeat(3, minmax(0,1fr));
		left: 0;
		position: absolute;
		right: 0;
		top: 0;
		transform: translateY(0%);
	}
	@media screen and (prefers-reduced-motion: no-preference) {
		.patreon__scroll {
			animation: avatarScroll 30s linear infinite;
		}
	}
	.patreon__scroll:hover {
		animation-play-state: paused;
	}
	@keyframes avatarScroll {
		0% {
			transform: translateY(0%);
		}
		100% {
			transform: translateY(-50%);
		}
	}
	.patreon__patron {
		align-items: center;
		display: flex;
		flex-direction: column;
	}
	.patreon__patron:not(:hover) {
		color: hsl(var(--accent));
	}
	.patreon__avatar {
		height: auto;
		width: 100%;
	}
	.patreon__username {
		display: block;
		max-width: 100%;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
	
	/* Sizing and order */
	@media (max-width:1399.9px) {
		.patreon__spacing .patreon__empty {
			height: 0;
			width: 0;
		}
	}
	@media(max-width:999.99px) {
		.patreon__container {
			flex-direction: column;
		}
		.patreon__text {
			margin-top: -1rem;
			padding-top: 0;
			width: 100% !important;
			z-index: 2;
		}
		.patreon__wall {
			margin: 0 auto;
			max-width: 100%;
			order: -1;
			width: 600px !important;
		}
	}
</style>








<div class="col c1">
	<div>
		<h1>
			<?php echo lang('Contribute', 'vkgyに参加する', ['container' => 'div']); ?>
		</h1>
	</div>
</div>
<div class="col c4">
	<div>
		<h3>
			How to add/edit
		</h3>
		<div class="text text--outlined">
			<ol>
				<li>Request admin permissions from <a class="user" href="/users/inartistic/">inartistic</a></li>
				<li>Check <a href="/blog/primer/">the primer</a> and documentation at the bottom of add/edit pages</li>
				<li>Jump in <a href="https://discord.gg/jw8jzXn" target="_blank">Discord</a> if you need help</li>
			</ol>
		</div>
	</div>
	<div>
		<h3 class="symbol__tag">
			<?php echo lang('Browse artist tags', 'アーティストタグ', ['container' => 'div']); ?>
		</h3>
		<div class="text text--outlined">
			<?php
				foreach($rslt_artist_tags as $tag) {
					echo '<span class="main__tag"><a href="/search/artists/?tags[]='.$tag["friendly"].'#result">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</a> <span class="any--weaken">&#215;'.$tag["num_tagged"].'</span></span>';
				}
			?>
		</div>
	</div>
	<div>
		<h3 class="symbol__tag">
			<?php echo lang('Browse release tags', 'リリースタグ', ['container' => 'div']); ?>
		</h3>
		<div class="text text--outlined">
			<?php
				foreach($rslt_release_tags as $tag) {
					echo '<span class="main__tag"><a href="/search/releases/?tag='.$tag["friendly"].'#result">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</a> <span class="any--weaken">&#215;'.$tag["num_tagged"].'</span></span>';
				}
			?>
		</div>
	</div>
	<div>
		<h3>
			Contact
		</h3>
		<div class="text text--outlined">
			<ul>
				<li><a href="mailto:johnathan.l.simpson@gmail.com">Email founder</a></li>
				<li><a href="https://twitter.com/vkgy_" target="_blank">Message on Twitter</a></li>
				<li><a href="https://facebook.com/vkgyofficial" target="_blank">Message on Facebook</a></li>
			</ul>
		</div>
	</div>
</div>

<div class="col c4-ABBC section__join any--signed-out-only">
	<div></div>

	<div>
		<h1 class="register__title">
			<?php echo lang('Join vkgy', '新規登録', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h1>
		
		<div class="any--flex col c2 register__wrapper">
			<form action="/account/function-register.php" class="text register__container register__section" enctype="multipart/form-data" method="post" name="register__form" autocomplete="off">
				<ul>
					<input class="any--hidden" id="register__radio--bat" name="register_avatar" type="radio" value="bat" checked />
					<input class="any--hidden" id="register__radio--gecko" name="register_avatar" type="radio" value="gecko" />
					
					<li>
						<div class="input__row">
							<div class="input__group">
								<label class="input__label"><?php echo lang('Username', 'ユーザー名', ['secondary_class' => 'any--hidden']); ?></label>
							</div>
							<input autocomplete="off" class="any--flex-grow" name="register_username" pattern="[A-z0-9-]+" placeholder="username (ユーザー名)" title="A-z, 0-9, -" />
						</div>
						
						<div class="any--weaken register__note">
							<?php echo lang('Usernames may contain: <strong>A-z</strong>, <strong>0-9</strong>, <strong>-</strong>. ', '（半角英字、数字、ハイフンを使用できます。）', ['secondary_class' => 'any--weaken-color']); ?>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label"><?php echo lang('Password', 'パスワード', ['secondary_class' => 'any--hidden']); ?></label>
								<input autocomplete="new-password" class="any--flex-grow symbol__locked" name="register_password" placeholder="password (パスワード)" type="password" />
							</div>
							<div class="input__group">
								<input class="register__show any--hidden" id="register__show" type="checkbox" />
								<label class="input__radio symbol__unchecked register__show-label" onclick="togglePassword()" for="register__show">Show?</label>
							</div>
						</div>
					</li>
					
					<li class="register__avatar-container">
						<div class="input__row">
							<div class="input__group" style="align-self: center;">
								<label class="input__label"><?php echo lang('Avatar eyes', 'アバター', ['secondary_class' => 'any--hidden']); ?></label>
								<label class="input__radio symbol__unchecked register__bat" for="register__radio--bat"><?php echo lang('bat', 'メークⅠ', ['secondary_class' => 'any--hidden']); ?></label>
								<label class="input__radio symbol__unchecked register__gecko" for="register__radio--gecko"><?php echo lang('gecko', 'メークⅡ', ['secondary_class' => 'any--hidden']); ?></label>
							</div>
							<div class="input__group">
								<div class="register__face"></div>
							</div>
						</div>
						
						<div class="any--weaken register__note">
							<?php echo lang('The avatar can be further customized after joining. ', '（登録後、アバターをさらにカスタマイズすることができます。）', ['secondary_class' => 'any--weaken-color']); ?>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow" data-role="submit-container">
								<button class="any--flex-grow" data-role="submit" name="register_submit" type="submit"><?php echo lang('Join vkgy', '登録する', ['secondary_class' => 'any--hidden']); ?></button>
								<span class="register__status" data-role="status"></span>
							</div>
						</div>
					</li>
				</ul>
				
				<div class="text text--outlined text--notice register__result" data-role="result"></div>
			</form>
			
			<div class="register__section">
				<h3>
					<?php echo lang('Why join?', '登録について', ['container' => 'div']); ?>
				</h3>
				<div class="text text--outlined">
					<ul class="ul--bulleted">
						<li><?php echo lang('Contribute information and news', '情報を追加する'); ?></li>
						<li><?php echo lang('Comment, review, rate, and tag', 'コメント・評価・レビュー'); ?></li>
						<li><?php echo lang('Customize your VK avatar', 'V系アバターを作る'); ?></li>
						<li><?php echo lang('Track items you own and want to own', 'コレクション・ほしい物リスト'); ?></li>
					</ul>
				</div>
				
				<h3>
					<?php echo lang('Already a member?', 'サインイン', ['container' => 'div']); ?>
				</h3>
				<form action="/account/function-sign_in.php" class="text text--outlined sign-in__container sign-in--refresh" enctype="multipart/form-data" method="post" name="form__main-signin">
					<div class="input__row">
						<div class="input__group">
							<label class="input__label"><?php echo lang('Sign in', 'サインイン', ['secondary_class' => 'any--hidden']); ?></label>
							<input class="any--flex-grow input" name="username" pattern="^[A-z0-9- \.]{3,}$" placeholder="username (ユーザー名)" autocomplete="username" />
							<input class="any--flex-grow input--secondary symbol__locked" name="password" placeholder="password (パスワード)" autocomplete="current-password" type="password" />
						</div>
						<div class="input__group" data-role="submit-container">
							<button class="any--flex-grow" data-role="submit"><?php echo lang('Sign in', 'サインイン', ['secondary_class' => 'any--hidden']); ?></button>
							<span class="register__status" data-role="status"></span>
						</div>
					</div>
					
					<div class="text text--outlined text--notice register__result" data-role="result"></div>
				</form>
			</div>
		</div>
	</div>
	
	<div></div>
</div>