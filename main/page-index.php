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
				<?php if( is_array($latest_item) && !empty($latest_item) && isset($latest_item['url']) ): ?>
					<a class="intro__card text any--flex" href="<?= $latest_item['url']; ?>" style="background-image:url(<?= $latest_item['image']['thumbnail_url']; ?>);">
						
						<span class="intro__card-image lazy" data-src="<?= $latest_item['image']['medium_url']; ?>"></span>
						
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
		
		<a class="browse__link browse--releases text" href="/releases/">
			<div class="h2 browse__title">
				<?= lang('release calendar', 'リリースカレンダー', 'div'); ?>
			</div>
		</a>
		
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
								
								<a class="news__image lazy" href="<?= $news_item['url']; ?>" data-src="<?= $news_item['image']['url']; ?>"></a>
								
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
										<h5 class="any--flex">
											<a class="user a--inherit comment__user" data-icon="<?= $comments[$i]['user']['icon']; ?>" data-is-vip="<?= $comments[$i]['user']['is_vip']; ?>" href="<?= $comments[$i]['user']['url']; ?>"><?= $comments[$i]['user']['username']; ?></a>
											<?php echo substr($comments[$i]["date_occurred"], 5); ?>
										</h5>
										
										<div class="any--flex">
											<a class="comment__content a--inherit" href="<?= $comments[$i]['url'] ? $comments[$i]['url'].'#comments' : '/comments/#comment-'.$comments[$i]['id']; ?>"><?= strip_tags($comments[$i]["content"]); ?></a>
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
					
					<?php if($_SESSION['is_vip']): ?>
						<p class="patreon__p">
							<?= $access_user->render_username($_SESSION); ?>, thank you so much!<br />
							vkgy is possible because of <a class="a--inherit" href="https://patreon.com/vkgy" target="_blank">Patreon</a> supporters like you.<br />
							<span style="font-size:1rem;">&ndash; <?= $access_user->render_username(['username' => 'inartistic']); ?></span>
						</p>
					<?php else: ?>
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
					<?php endif; ?>
					
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