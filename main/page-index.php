<?php
	page_header('Welcome to vk.gy', 'vk.gyへようこそ');
	
	subnav([
		lang('Patreon', 'パトレオン', ['secondary_class' => 'any--hidden']) => 'https://patreon.com/vkgy/',
	], 'interact');
	
	script([
		'/scripts/script-signIn.js',
	]);
?>

<div class="col c4-ABBC section__main">
	<div class="main__left">
		<div class="main__aod">
			<h3>
				<?php echo lang('Artist of the day', '今日のアーティスト', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h3>
			<div class="card--small">
				<?php $access_artist->artist_card($artist_of_day); ?>
			</div>
		</div>
		
		<?php
			/*include('../php/function-get_cdj_preorders.php');
			$cdj_preorders = get_cdj_preorders();
			$cdj_preorders = array_slice($cdj_preorders['list'], 0, 4);
			
			if(is_array($cdj_preorders) && !empty($cdj_preorders)) {
				?>
					<h3>
						<?= lang('Preorder', 'プレオーダー', 'div'); ?>
					</h3>
					<ul class="text text--outlined">
						<?php
							foreach($cdj_preorders as $preorder) {
								?>
									<li><a href="http://www.cdjapan.co.jp/aff/click.cgi/e86NDzbdSLQ/6128/A549875/detailview.html%3FKEY%3D<?= $preorder['key']; ?>" target="_blank"><?= $preorder['artist'].' &ldquo;'.$preorder['title'].'&rdquo;'; ?></a></li>
								<?php
							}
						?>
					</ul>
				<?php
			}*/
		?>
		
		
		<div class="main__iod">
			<h3>
				<?php echo lang('Flyer of the day', '今日のフライヤー', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h3>
			<div class="iod__container text text--outlined text--compact">
				<a class="iod__link" href="<?php echo $image["url"]; ?>" target="_blank">
					<img class="iod__image <?php echo $image["is_wide"] ? "iod--wide" : null; ?> lazy" data-src="<?= str_replace(".", ".medium.", $image["url"]); ?>" />
				</a>
				<a class="artist" data-name="<?= $image['artists'][0]['name']; ?>" href="<?= '/artists/'.$image['artists'][0]['friendly'].'/'; ?>">
					<?= lang(($image['artists'][0]['romaji'] ?: $image['artists'][0]['name']), $image['artists'][0]['name'], 'hidden'); ?>
				</a>
			</div>
		</div>
		
		<div class="main__ranking">
			<h3>
				<div class="h5">
					<?= date('n/j', strtotime("-2 weeks sunday", time())).'~'.date('n/j', strtotime("-1 weeks sunday", time())); ?>
				</div>
				<?php echo lang('Band access ranking', 'アクセスランキング', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h3>
			<ol class="text text--outlined text--compact ul--compact">
				<?php
					foreach($rslt_rankings as $ranking_num => $ranking) {
						?>
							<li class="ranking__item">
								<span class="ranking__number symbol__user"></span>
								<a class="artist artist--no-symbol" href="/artists/<?php echo $ranking["friendly"]; ?>/"><?= lang($ranking['quick_name'], $ranking['name'], 'hidden'); ?></a>
							</li>
						<?php
					}
				?>
			</ol>
		</div>
		
		<div class="main__ranking">
			<h3>
				<div class="h5">
					<?= date('n/j', strtotime("-2 weeks sunday", time())).'~'.date('n/j', strtotime("-1 weeks sunday", time())); ?>
				</div>
				<?php echo lang('User points ranking', 'ユーザーランキング', 'div'); ?>
			</h3>
			<ol class="text text--outlined text--compact ul--compact">
				<?php
					foreach($point_ranking as $ranking_num => $user_points) {
						?>
							<li class="ranking__item">
								<span class="ranking__number symbol__user"></span>
								<span class="any__note" style="float:right;"><?= $user_points['points_value'].' '.lang('pt', '点', 'hidden'); ?></span>
								<a href="<?= '/users/'.$user_points['username'].'/'; ?>"><?= $user_points['username']; ?></a>
								<span class="any--weaken" style=""><?= 'LV '.$user_points['level']; ?></span>
							</li>
						<?php
					}
				?>
			</ol>
		</div>
	</div>

	<div class="main__middle">
		<div class="main__news">
			<h2>
				<?php echo lang('Visual kei news', 'ビジュアル系ニュース', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h2>
			
			<div class="text any--flex news__container">
				<div class="news__main lazy any__obscure" data-src="<?php echo !empty($news[0]['image']) ? $news[0]['image']['url'] : null; ?>">
					<h2>
						<div class="h5 any--flex">
							<?php echo $news[0]["date_occurred"]; ?>
							<a class="user a--inherit news__user" data-icon="<?= $news[0]['user']['icon']; ?>" data-is-vip="<?= $news[0]['user']['is_vip']; ?>" href="<?= $news[0]['user']['url']; ?>"><?= $news[0]['user']['username']; ?></a>
						</div>
						<a href="/blog/<?php echo $news[0]["friendly"]; ?>/"><?php echo $news[0]["title"]; ?></a>
					</h2>
					<div class="news__content">
						<?php echo $news[0]["content"]; ?>
					</div>
					<a class="any--weaken-color a--padded a--outlined" href="/blog/<?php echo $news[0]["friendly"]; ?>/"><?php echo $news[0]["comment_text"]; ?></a>
				</div>
				
				<ul class="news__additional">
					<?php
						for($i=1; $i<=5; $i++) {
							?>
								<li class="news__entry any__obscure lazy" data-src="<?php echo !empty($news[$i]['image']) ? $news[$i]['image']['url'] : null; ?>">
									<h3>
										<a href="/blog/<?php echo $news[$i]["friendly"]; ?>/"><?php echo $news[$i]["title"]; ?></a>
									</h3>
								</li>
							<?php
						}
					?>
				</ul>
				
				<div class="news__features any--flex" style="padding-bottom:0;">
					<?php
						$featured_articles = $access_blog->access_blog([ 'tag' => 'feature', 'get' => 'basics', 'limit' => 5 ]);
						
						// Shuffle featured interviews
						$latest_article = array_shift($featured_articles);
						shuffle($featured_articles);
						array_unshift($featured_articles, $latest_article);
						
						foreach($featured_articles as $article) {
							if($article['id'] != (969 * 4)) {
								$image = '/images/'.$article['image_id'].'-'.$article['friendly'].'.medium.';
								$image = file_exists('../images/image_files/'.$article['image_id'].'.jpg') ? $image.'jpg' : $image.'png';
								?>
									<a class="news__entry news__feature" href="<?= '/blog/'.$article['friendly'].'/'; ?>" style="<?= 'background-image:url('.$image.');'; ?>">
										<div>
											<?= $article['title']; ?>
										</div>
									</a>
								<?php
							}
						}
					?>
					<a class="h5 news__title" href="/interview/">
						<?= lang('Interviews', 'インタビュー', 'hidden'); ?>
					</a>
				</div>
				
				<div class="any__obscure news__entry news__vip any--flex">
					<div>
						<h5>VIP-limited update</h5>
						<?php
							if($_SESSION['is_vip']) {
								$sql_vip = 'SELECT vip.title, vip.friendly, vip_views.user_id AS is_viewed FROM vip LEFT JOIN vip_views ON (vip_views.post_id=vip.id AND vip_views.user_id=?) ORDER BY date_occurred DESC LIMIT 1';
								$stmt_vip = $pdo->prepare($sql_vip);
								$stmt_vip->execute([ $_SESSION['user_id'] ]);
								$rslt_vip = $stmt_vip->fetch();
								
								?>
									<p>
										<a class="symbol__vip" href="<?php echo '/vip/'.$rslt_vip["friendly"].'/'; ?>">[VIP] <?php echo $rslt_vip["title"]; ?></a>
										<?php echo !$rslt_vip["is_viewed"] ? '<span class="news__new any--weaken-size">NEW</span>' : null; ?>
									</p>
								<?php
							}
							else {
								?>
									<span class="symbol__vip"><?php echo lang('This content is <a href="https://patreon.com/vkgy/" target="_blank">VIP-limited</a>.', 'このコンテンツは<a href="https://patreon.com/vkgy/" target="_blank">VIP限定</a>です。', ['secondary_class' => 'any--hidden']); ?></span>
								<?php
							}
						?>
					</div>
					<?php
							if($_SESSION['is_vip']) {
								?>
									<a class="a--padded a--outlined" href="/vip/" style="margin-left: auto;">VIP section</a>
								<?php
							}
							else {
								?>
									<a class="a--padded a--outlined" href="https://www.patreon.com/vkgy/" target="_blank" style="margin-left: auto; white-space: nowrap;"><?php echo lang('Support us', 'パトレオン', ['secondary_class' => 'any--hidden']); ?></a>
								<?php
							}
					?>
				</div>
			</div>
		</div>

		<div class="support__container any--margin">
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

		<h3>
			<?php echo lang('Recent discussions', '最近のコメント', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h3>
		<input class="obscure__input" id="obscure-comments" type="checkbox" checked="">
		<div class="text obscure__container obscure--long comments__container">
			<ul>
				<?php
					for($i=0; $i<count($comments); $i++) {
						
						$comment_class = null;
						$comments[$i]['user']['avatar_url'] = '/usericons/avatar-'.(file_exists('../usericons/avatar-'.$comments[$i]['user']['username'].'.png') ? $comments[$i]['user']['username'] : 'anonymous').'.png?'.date('YmdH');
						
						if(!$comments[$i]['is_approved']) {
							$comment_class .= ($_SESSION['can_approve_data'] ? 'comment--unapproved' : 'any--hidden');
						}
						
						?>
							<li class="obscure__item <?php echo $comment_class; ?>">
								<div class="any--flex">
									<a class="comment__avatar-container" data-icon="<?= $comments[$i]['user']['icon']; ?>" data-is-vip="<?= $comments[$i]['user']['is_vip']; ?>" href="<?= $comments[$i]['user']['url']; ?>">
										<img alt="<?php echo $comments[$i]['user']['username']; ?>'s avatar" class="comment__avatar" src="<?php echo $comments[$i]['user']['avatar_url']; ?>" />
									</a>
									
									<div class="comment__comment">
										<h5 class="any--flex">
											<a class="user a--inherit comment__user" data-icon="<?= $comments[$i]['user']['icon']; ?>" data-is-vip="<?= $comments[$i]['user']['is_vip']; ?>" href="<?= $comments[$i]['user']['url']; ?>"><?= $comments[$i]['user']['username']; ?></a>
											<?php echo substr($comments[$i]["date_occurred"], 5); ?>
										</h5>
										
										<div class="any--flex">
											&ldquo;<span class="comment__content"><?php echo $comments[$i]["content"]; ?></span>&rdquo;
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
			<label class="input__button obscure__button" for="obscure-comments">Show more</label>
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

<div class="col c2 any--margin" style="background:hsl(var(--background)); padding-top: 3rem;">
	<div class="">
		<h1>
			<?php echo lang('Patreon supporters', 'サポーター', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h1>

		<div class="text text--notice any--weaken-color">
			<ul class="ul--inline ">
				<?php
					if(is_array($rslt_vip_users) && !empty($rslt_vip_users)) {
						$rslt_vip_users[] = ["username" => "redaudrey"];
						foreach($rslt_vip_users as $user) {
							?>
								<li><a class="user" href="/users/<?php echo $user["username"]; ?>/"><?php echo $user["username"]; ?></a></li>
							<?php
						}
					}
				?>
				<li><a class=" symbol__next a--inherit" href="https://www.patreon.com/vkgy/" target="_blank">Become VIP</a></li>
			</ul>
		</div>
	</div>

	<div class="any--flex">
		<div class="text text--outlined">
			VIP supporters receive:

			<ul class="ul--bulleted ul--inline support__list" style="margin-left: 2rem;">
				<li>VIP badge</li>
				<li>Early access</li>
				<li>Premium support</li>
				<li>Premium images</li>
				<li>Discord channel</li>
				<li>Dev blog</li>
				<li>Avatar items</li>
				<li>and more</li>
			</ul>

			<p>
				<a class="a--padded a--outlined any--weaken-color" href="https://www.patreon.com/vkgy/" target="_blank" style="text-indent: 0;">Support vkgy at Patreon</a>
			</p>
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