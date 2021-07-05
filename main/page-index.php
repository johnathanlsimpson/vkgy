<?php

style([
	"/main/style-page-index.css"
]);

script([
	'/main/script-page-index.js',
	'/scripts/script-signIn.js'
]);

breadcrumbs([
	"Home" => "https://vk.gy/"
]);

$page_title = 'Visual kei bands, news, profiles | ビジュアル系 バンド、ニュース、歴史';
$page_description = 'The visual kei library: bands, subgenres, news, history, forum, and more. ビジュアル系 バンド、ニュース、歴史、ランキング';

$background_image = null;

/* Page title */
$page_header = lang('Welcome to vkgy', 'vkgyへようこそ', 'hidden');

/* Intro */
ob_start();
?>
	<!-- Introductory paragraph -->
	<div class="col c2">
		
		<!-- Intro paragraph -->
		<div class="home-head__text any--margin">
			
			<div class="home-head__title h1">
				<?= lang('Welcome to vkgy,', 'vkgyへようこそ―', 'hidden'); ?>
			</div>
			<p class="home-head__p">
				<?= lang(
					'the visual kei library. Track your collection, interact with fans, learn hidden history, and get the latest news and interviews.',
					'vkgyはビジュアル系のライブラリです。ここでは、コレクションを追跡されます、バンドの歴史を記録する、と最新のニュースを集まる。',
					'hidden' ); ?>
			</p>
			<div class="home-head__more">
				<a class="symbol__arrow" href=""><?= lang('About vkgy', 'vkgyとは', 'hidden'); ?></a>
				<a class="symbol__arrow" href=""><?= lang('What\'s vkei?', 'ビジュアル系とは', 'hidden'); ?></a>
			</div>
			
		</div>
		
		<!-- Intro Patreon link -->
		<div class="home-head__support any--margin">
			
			<a class="home-head__patreon symbol__patreon" href="https://patreon.com/vkgy" target="_blank">Support Us</a>
			<span class="home-head__supported">Thank you to our kind Patrons, who make vkgy possible.</span>
			
		</div>
		
	</div>
	
	<!-- Featured cards -->
	<div class="col c1 intro__cards-container any--scrollbar any--permanent-scrollbar">
		<div class="intro__cards">
			
			<?php foreach($latest_items as $item_key => $latest_item): ?>
				<?php if( is_array($latest_item) && !empty($latest_item) && isset($latest_item['url']) ): ?>
					<a class="intro__card text any--flex" href="<?= $latest_item['url']; ?>" style="background-image:url(<?= $latest_item['image']['thumbnail_url'] ?: null; ?>);">
						
						<span class="intro__card-image lazy" data-src="<?= $latest_item['image']['medium_url'] ?: null; ?>"></span>
						
						<div class="intro__card-title h2">
							<span class="intro__card-pill h5"><?= $latest_item['pill']; ?></span>
							<div class="intro__card-text"><?= $latest_item['title']; ?></div>
						</div>
						
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
			
		</div>
	</div>
<?php

$GLOBALS['page_header_supplement'] = ob_get_clean();

?>

<div class="col c4-ABBC section__main">
	<div class="main__left">
		
		<a class="browse__link browse--artists text" href="/artists/">
			<div class="h2 browse__title">
				<?= lang('browse artists', 'アーティスト一覧', 'div'); ?>
			</div>
		</a>
		<span class="any--weaken" style="display:block;height:0;transform:translateY(-3rem);">art by <a class="a--inherit" href="https://www.instagram.com/darrylpyon/" target="_blank">@darrylpyon</a></span>
		
			<a class="browse__link browse--releases text" href="/releases/" style="background-image:none;">
				
				<div style="bottom:0;left:0;overflow:hidden;position:absolute;right:0;top:-10%;">
					<span class="browse__cover"><img class="browse__thumb" src="<?= '/images/'.$covers[0]['id'].'.thumbnail.'.$covers[0]['extension']; ?>" /></span>
					<span class="browse__cover"><img class="browse__thumb" src="<?= '/images/'.$covers[1]['id'].'.thumbnail.'.$covers[1]['extension']; ?>" /></span>
					<span class="browse__cover"><img class="browse__thumb" src="<?= '/images/'.$covers[2]['id'].'.thumbnail.'.$covers[2]['extension']; ?>" /></span>
				</div>
				<div class="h2 browse__title">
					<?= lang('release calendar', 'リリースカレンダー', 'div'); ?>
				</div>
			</a>
		
		<style>
			.browse--releases {
			}
			.browse__cover {
				height: auto;
				left: 0;
				overflow: hidden;
				position: absolute;
				top: 0;
				transform-origin: left top;
				width: 50%;
			}
			.browse__cover:nth-of-type(1) {
				background: hsla(var(--background--bold), 0);
				/*left: 52%;
				top: -10%;
				transform: rotate(26deg);*/
				transform: translateX(105%) translateY(0%) rotate(26deg);
			}
			.browse__cover:nth-of-type(2) {
				background: hsla(var(--text--secondary), 0);
				/*right: -7%;
				left: 57%;
				top: 40%;
				transform: rotate(6deg);*/
				transform: translateX(122%) translateY(60%) rotate(6deg);
				
				/*box-shadow: 0 0 0 4px orange;
				opacity: 0.75;*/
			}
			.browse__cover:nth-of-type(3) {
				background: hsla(var(--text), 0);
				/*left: -10%;
				top: 20%;
				transform: rotate(-16deg);*/
				transform-origin: right top;
				transform: translateX(-20%) translateY(24%) rotate(-16deg);
			}
			.browse__thumb {
				height: auto;
				object-fit: cover;
				object-position: center top;
				width: 105%;
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
	</div>
	
	<div class="main__middle col c2">
		
		<div class="main__middle-left">
			
			<!-- News -->
			<ul class="news__container2 text">
				<?php
					foreach($news as $news_item) {
						?>
							<li class="news__item2 any--flex">
								
								<a class="news__image lazy" href="<?= $news_item['url']; ?>" data-src="<?= $news_item['image']['url'] ?: null; ?>"></a>
								
								<div class="news__text">
									<div class="news__supertitle h5">
										<?= $news_item['date_occurred']; ?>
										<a class="user a--inherit" data-icon="<?= $news_item['user']['icon']; ?>" data-is-vip="<?= $news_item['user']['is_vip']; ?>" href="<?= $news_item['user']['url']; ?>"><?= $news_item['user']['username']; ?></a>
									</div>
									
									<a class="news__title2" href="<?= $news_item['url']; ?>"><?= $news_item['title']; ?></a>
								</div>
								
							</li>
						<?php
					}
				?>
			</ul>
		</div>
		
		<div class="main__middle-right">
			
			<!-- Comments -->
			<ul class="comments__container2 any--margin">
				<?php
					
					// Set helper for adding symbol to links later
					$symbol_classes = [ 'blog' => 'news', 'artist' => 'artist', 'release' => 'release', 'development' => 'news' ];
					
					// Only get 10 comments
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
										<img class="comment__avatar lazy" data-src="<?= $comments[$i]['user']['avatar_url']; ?>" />
									</a>
									
									<div class="comment__comment">
										<div class="any--flex" style="white-space:nowrap;width:100%;overflow:hidden;max-width:100%;">
											<div class="h5">
												<a class="user a--inherit" data-icon="<?= $comments[$i]['user']['icon']; ?>" data-is-vip="<?= $comments[$i]['user']['is_vip']; ?>" href="<?= $comments[$i]['user']['url']; ?>"><?= $comments[$i]['user']['username']; ?></a>
												<?= substr($comments[$i]["date_occurred"], 5); ?>
											</div>
											
											<div class="any--weaken any--flex" style="margin-left:auto;padding-left:1rem;max-width:100%;overflow:hidden;transform:translateY(-1px);">
												<?php
													
													// Set the symbol for the link to the item
													$symbol_class = $symbol_classes[ $comments[$i]['item_type'] ] ?: ($comments[$i]['item_type'] === 'none' ? 'comment' : $comments[$i]['item_type']);
													$symbol_class = 'symbol__'.$symbol_class;
													
													// Show item name if possible, otherwise assume generic comment
													if( $comments[$i]['item_type'] != 'none' ) {
														$link_text = $comments[$i]['item_romaji'] ? lang($comments[$i]['item_romaji'], $comments[$i]['item_name'], 'hidden') : $comments[$i]['item_name'];
													}
													else {
														$link_text = 'comments';
													}
													
													// If item type is set but no url found, assume page was deleted and don't show link
													if( $comments[$i]['item_type'] != 'none' && $comments[$i]['item_url'] === '/comments/' ) {
														$link = 'a deleted page';
													}
													else {
														$link  = '<a class="a--inherit '.$symbol_class.'" href="'.$comments[$i]['item_url'].'" style="max-width:100%;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">'.$link_text.'</a>';
													}
													
													echo 'on&nbsp;'.$link;
													
												?>
											</div>
											
										</div>
										
										<div class="any--flex">
											<a class="comment__content a--inherit" href="<?= $comments[$i]['item_url'] ? $comments[$i]['item_url'].'#comments' : '/comments/#comment-'.$comments[$i]['id']; ?>"><?= strip_tags($comments[$i]["content"]); ?></a>
											<a class="comment__next symbol__next" href="<?= $comments[$i]['item_url'] ? $comments[$i]['item_url'].'#comments' : '/comments/#comment-'.$comments[$i]['id']; ?>">Read</a>
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
	</div>
	
	<div class="main__right">
		<div class="main__updates">
			<h3>
				Recent artist edits
			</h3>
			
			<ul class="text text--outlined" style="z-index: 1;">
				<?php
					for($i=0; $i<6; $i++) {
						?>
							<li class="recent-artist__item">

								<a class="recent-artist__link" href="<?= $updates[$i]['artist_url']; ?>"></a>

								<span class="recent-artist__image" style="<?= $updates[$i]['image_id'] ? 'background-image:url('.$updates[$i]['artist_url'].'main.thumbnail.jpg);' : null; ?>"></span>

								<a class="artist" href="<?= $updates[$i]['url']; ?>"><?= $updates[$i]['artist_romaji'] ? lang($updates[$i]['artist_romaji'], $updates[$i]['artist_name'], 'parentheses') : $updates[$i]['artist_name']; ?></a>

							</li>
						<?php
					}
				?>
			</ul>
			
			<h3>
				Recent release edits
			</h3>
			
			<ul class="text text--outlined " style="z-index: 1;">
				<?php
					$updates = array_slice($updates, 6);
					
					for($i=0; $i<6; $i++) {
						if($updates[$i]) {
							?>
								<li>
									
									<a class="artist any--weaken-size" href="<?= $updates[$i]['artist_url']; ?>"><?= $updates[$i]['artist_romaji'] ? lang($updates[$i]['artist_romaji'], $updates[$i]['artist_name'], 'hidden') : $updates[$i]['artist_name']; ?></a>
									
									<br />
									
									<a class="symbol__release" href="<?= $updates[$i]['url']; ?>"><?= $updates[$i]['romaji'] != $updates[$i]['name'] ? lang($updates[$i]['romaji'], $updates[$i]['name'], 'hidden') : $updates[$i]['name']; ?></a>
									
									<?= $updates[$i]['num_items'] > 1 ? '<span class="any__note">+'.($updates[$i]['num_items'] - 1).' more</span>' : null; ?>
									
								</li>
							<?php
						}
					}
				?>
			</ul>
				
				<style>
					.recent-artist__item.recent-artist__item {
						padding-right: calc(50px + 0.5rem);
					}
					.recent-artist__link {
						bottom: 0;
						left: 0;
						position: absolute;
						right: 0;
						top: 0;
						z-index: 1;
					}
					.recent-artist__link:hover ~ a {
						color: hsl(var(--interactive));
					}
					.recent-artist__link:hover ~ .recent-artist__image {
						opacity: 0.75;
					}
					.recent-artist__image {
						background: hsl(var(--background));
						background-position: center;
						background-size: cover;
						bottom: 0;
						position: absolute;
						right: -1rem;
						top: 0;
						width: 50px;
					}
					.recent-artist__item:first-of-type .recent-artist__image {
						top: -1rem;
					}
					.recent-artist__item:last-of-type .recent-artist__image {
						bottom: -1rem;
					}
				</style>
				
		</div>
	</div>
</div>

<?php if( !$_SESSION['is_vip'] || $_SESSION['username'] === 'inartistic' ): ?>
<div class="col c1">
	
	<div class="any--flex" style="justify-content:space-around;">
		
		<div class="any--margin" style="max-width:100%;width:728px;">
			<div class="any--weaken" style="background:hsl(var(--background));border-radius:3px 3px 0 0;display:inline-block;line-height:1;padding:0.5rem;width:auto;">
				<span class="h5">AD</span> | 
				<span class=""><a href="https://patreon.com/vkgy" target="_blank">Please consider becoming a VIP member</a> to remove ads and support vkgy.</span>
			</div><br />
			
			<?php
				$ads = [
					'<a class="support__link" href="http://rarezhut.net/" target="_blank"><img alt="RarezHut" class="support__image lazy" data-src="/main/ad-rarezhut-wide.png" /></a>',
					'<a class="support__link" href="https://discord.gg/jw8jzXn" target="_blank"><img alt="vkgy at Discord" class="support__image lazy" data-src="/main/ad-discord.png" /></a>',
					'<a class="support__link" href="http://witchthrone.com/" target="_blank"><img alt="Witchthrone" class="support__image lazy" data-src="/main/ad-witchthrone-wide.gif" /></a>',
					'<a class="support__link" href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/" target="_blank"><img alt="Buy vk merch at CDJapan" class="support__image lazy" data-src="/main/ad-cdjapan-wide.jpg" /></a>',
				];
				
				shuffle($ads);
				
				echo $ads[0];
			?>
		</div>
		
	</div>
	
</div>
<?php endif; ?>

<!-- Videos -->
<div class="col c1 any--margin">
	<div>
			<a class="a--outlined a--padded" href="/videos/" style="float:right;z-index:1;">all videos</a>
		<h1>
			<?= lang('Latest MV', '最新MV', 'div'); ?>
		</h1>
	</div>
	<div class="videos__container">
		<?php
			$access_video = new access_video($pdo);
			$videos = $access_video->access_video([ 'type' => $access_video->video_types['mv'], 'is_approved' => true, 'get' => 'basics', 'limit' => 4 ]);
			
			foreach($videos as $video) {
				?>
					<div class="video__container">
						
						<a class="video__thumbnail lazy" data-src="<?= $video['thumbnail_url']; ?>" href="<?= '/videos/'.$video['id'].'/'; ?>"></a>
						
						<a class="video__artist lazy" data-src="<?= '/artists/'.$video['artist']['friendly'].'/main.thumbnail.jpg'; ?>" href="<?= '/artists/'.$video['artist']['friendly'].'/'; ?>"></a>
						
						<a class="video__link artist any--weaken-size" href="<?= '/artists/'.$video['artist']['friendly'].'/'; ?>">
							<?= lang($video['artist']['romaji'] ?: $video['artist']['name'], $video['artist']['name'], 'hidden'); ?>
						</a>
						
						<br />
						
						<a class="video__link symbol__video" href="<?= '/videos/'.$video['id'].'/'; ?>"><?= $access_video->clean_title($video['youtube_name'], $video['artist']); ?></a>
						
					</div>
				<?php
			}
		?>
	</div>
</div>
<style>
	.videos__container {
		--num-columns: 1;
		display: grid;
		grid-gap: var(--gutter);
		grid-template-columns: repeat(var(--num-columns), minmax(0,1fr));
	}
	@media(min-width: 500px) {
		.videos__container {
			--num-columns: 2;
		}
	}
	@media(min-width: 800px) {
		.videos__container {
			--num-columns: 3;
		}
		.video__container:last-of-type {
			display: none;
		}
	}
	@media(min-width: 1000px) {
		.videos__container {
			--num-columns: 4;
		}
		.video__container:last-of-type {
			display: block;
		}
	}
	.video__container {
		padding-right: 1rem;
	}
	.video__link {
		display: inline-block;
		line-height: 1rem;
	}
</style>

<?php include('partial-patreon.php'); ?>

<div class="col c1">
	<div>
		<h1>
			<?php echo lang('Contribute', 'vkgyに参加する', ['container' => 'div']); ?>
		</h1>
	</div>
</div>
<div class="col c4">
	
	<div>
		<?php
			$sql_development = 'SELECT id, title, friendly, content, SUBSTRING(date_occurred,1,10) AS date_occurred FROM development WHERE is_issue=? ORDER BY date_occurred DESC LIMIT 5';
			$stmt_development = $pdo->prepare($sql_development);
			$stmt_development->execute([ 0 ]);
			$development = $stmt_development->fetch();
			
			$development['content'] = $markdown_parser->parse_markdown($development['content']);
			$development['content'] = str_replace('</li>', '<br />', $development['content']);
			$development['content'] = strip_tags( $development['content'], ['br'] );
			$development['content'] = str_replace('Here are today&#39;s development updates. As always, thank you for supporting vkgy!', '', $development['content']);
			$development['length'] = strlen($development['content']);
		?>
		<h3>
			<?= lang('Development update', 'サイト更新', 'div'); ?>
		</h3>
		
		<input class="obscure__input" type="checkbox" checked />
		<div class="dev__container text text--outlined obscure__container obscure--faint">
			
			<a class="card__link" href="/development/"></a>
			
			<div class="h5"><?= $development['date_occurred']; ?></div>
			
			<?= substr( $development['content'], 0, 200 ).( $development['length'] > 200 ? '...' : null ); ?>
			
			<a class="dev__link a--padded a--outlined" href="/development/" style="margin-top: 1rem;">track development</a>
			
		</div>
		
		<div class="any--weaken symbol__help" style="margin-top: -2rem; margin-bottom: 3rem;">
			Please consider supporting our development through <a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">vkgy's Patreon</a>.
		</div>
		
		<style>
			.dev__container::after {
				bottom: 4rem;
				opacity: 1;
			}
			.dev__link {
				display: block;
				max-width: 100%;
				overflow: hidden;
				text-align: center;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
			.card__link:hover ~ .dev__link {
				color: hsl(var(--interactive));
			}
		</style>
		
		<h3>
			<?= lang('About vkgy', 'vkgyについて', 'div'); ?>
		</h3>
		<ul class="text text--outlined">
			<li><a href="/about/">About</a></li>
			<li><a href="/about/">Privacy policy</a></li>
			<li><a href="/about/">Contact</a></li>
		</ul>
		
	</div>
	
	<div>
		<h3 class="">
			<?php echo lang('Browse artist tags', 'アーティストタグ', ['container' => 'div']); ?>
		</h3>
		<div class="text text--outlined" style="white-space:normal;">
			<?php
				foreach($artist_tags as $tag) {
					echo '<span class="main__tag" style="white-space:nowrap;margin-right:1.5ch;"><a href="/search/artists/?tags[]='.$tag["friendly"].'#result">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</a> <span class="any--weaken">&#215;'.$tag["num_tagged"].'</span></span> ';
				}
			?>
		</div>
	</div>
	<div>
		<h3 class="">
			<?php echo lang('Browse release tags', 'リリースタグ', ['container' => 'div']); ?>
		</h3>
		<div class="text text--outlined">
			<?php
				foreach($release_tags as $tag) {
					echo '<span class="main__tag" style="white-space:nowrap;margin-right:1.5ch;"><a href="/search/releases/?tag='.$tag["friendly"].'#result">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</a> <span class="any--weaken">&#215;'.$tag["num_tagged"].'</span></span> ';
				}
			?>
		</div>
	</div>
	<div>
		<h3>
			<?= lang('Translate vkgy', 'vkgyを翻訳', 'div'); ?>
		</h3>
		<ul class="text text--outlined ul--compact">
			<?php
				$sql_untranslated = '
					SELECT
						SUM(ISNULL(ja_id)) AS num_ja,
						SUM(ISNULL(de_id)) AS num_de,
						SUM(ISNULL(es_id)) AS num_es,
						SUM(ISNULL(fr_id)) AS num_fr,
						SUM(ISNULL(ko_id)) AS num_ko,
						SUM(ISNULL(nl_id)) AS num_nl,
						SUM(ISNULL(ru_id)) AS num_ru,
						SUM(ISNULL(zh_id)) AS num_zh
					FROM
						translations
				';
				$stmt_untranslated = $pdo->prepare($sql_untranslated);
				$stmt_untranslated->execute();
				$rslt_untranslated = $stmt_untranslated->fetch();
				
				foreach([
					'ja' => '日本語',
					'de' => 'Deutsch',
					'es' => 'Español',
					'fr' => 'Français',
					'ko' => '한국어',
					'nl' => 'Nederlands',
					'ru' => 'Русский',
					'zh' => '中文',
				] as $key => $name) {
					echo '<li>';
					echo $name.' ';
					echo '<span class="any--weaken">'.$rslt_untranslated[ 'num_'.$key ].' needed</span>';
					echo '</li>';
				}
				
			?>
			<li><a class="symbol__plus" href="/translations/">translate</a></li>
		</ul>
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
					<input class="any--hidden symbol--orphan-a" id="register__radio--bat" name="register_avatar" type="radio" value="bat" checked />
					<input class="any--hidden symbol--orphan-b" id="register__radio--gecko" name="register_avatar" type="radio" value="gecko" />
					
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
								<label class="input__radio register__show-label" for="register__show">
									<input class="input__choice register__show any--hidden" id="register__show" onclick="togglePassword()" type="checkbox" />
									<span class="symbol__unchecked">show?</span>
								</label>
							</div>
						</div>
					</li>
					
					<li class="register__avatar-container symbol--parent">
						<div class="input__row">
							<div class="input__group" style="align-self: center;">
								<label class="input__label"><?php echo lang('Avatar eyes', 'アバター', ['secondary_class' => 'any--hidden']); ?></label>
								<label class="input__radio register__bat symbol--orphan-a" for="register__radio--bat"><span class="symbol__unchecked"><?php echo lang('bat', 'メークⅠ', ['secondary_class' => 'any--hidden']); ?></span></label>
								<label class="input__radio register__gecko symbol--orphan-b" for="register__radio--gecko"><span class="symbol__unchecked"><?php echo lang('gecko', 'メークⅡ', ['secondary_class' => 'any--hidden']); ?></span></label>
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

<style>
.main__middle-left + .google-auto-placed {
    display: none !important;
}
</style>