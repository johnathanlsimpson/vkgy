<div class="col c4-AAAB any--margin">
	<div class="any--weaken">
		<?php
			echo
			lang(
				'vkgy is a visual kei library maintained by overseas fans. It specializes in small bands, and hard-to-find information.',
				'vkgy（ブイケージ）はビジュアル系のライブラリです。関連するアーティストのメンバープロフィールや活動やリリース情報などがあります。',
				[
					'primary_container' => 'p',
					'secondary_container' => 'p',
				]
			);
		?>
	</div>
	
	<div class="cta__container">
		<a class="a--padded a--outlined cta__link a--patreon" href="https://patreon.com/vkgy/" target="_blank"><img src="/style/logo-patreon.png" style="height: 1rem;" /> <?php echo lang('Support vkgy', 'パトレオン', ['secondary_class' => 'any--hidden']); ?></a>
	</div>
</div>

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
		
		<div class="main__iod">
			<h3>
				<?php echo lang('Flyer of the day', '今日のフライヤー', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h3>
			<div class="text text--outlined">
				<p class="any__obscure iod__container">
					<a class="iod__link"  href="<?php echo $image["url"]; ?>" target="_blank">
						<img class="iod__image <?php echo $image["is_wide"] ? "iod--wide" : null; ?> lazy" data-src="<?php echo str_replace(".", ".medium.", $image["url"]); ?>" />
					</a>
				</p>
				<p>
					<a class="artist" data-name="<?php echo $image["artist_name"]; ?>" href="/artists/<?php echo $image["artist_friendly"]; ?>/"><?php echo $image["artist_quick_name"]; ?></a>
				</p>
				<p class="any--weaken">
					<a class="symbol__vip" href="https://www.patreon.com/vkgy/" target="_blank">VIP members</a> can access high-res, unwatermarked version. <a href="/images/&type=flyer">View past flyers?</a>
				</p>
			</div>
		</div>
		
		<div class="main__ranking">
			<h3>
				<?php echo lang('Weekly artist ranking', '週間ランキング', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h3>
			<div class="text text--outlined">
				<ol>
					<?php
						foreach($rslt_rankings as $ranking) {
							?>
								<li>
									<a class="artist" href="/artists/<?php echo $ranking["friendly"]; ?>/"><?php echo $ranking["quick_name"]; ?></a>
								</li>
							<?php
						}
					?>
				</ol>
			</div>
		</div>
	</div>
	
	<div class="main__middle">
		<div class="main__news">
			<h2>
				<?php echo lang('Visual kei news', 'ビジュアル系ニュース', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
			</h2>
			
			<div class="text any--flex news__container">
				<div class="news__main lazy any__obscure" data-src="<?php echo $news[0]["image"]; ?>">
					<h2>
						<div class="h5 any--flex">
							<?php echo $news[0]["date_occurred"]; ?>
							<a class="user a--inherit news__user" href="/users/<?php echo $news[0]["username"]; ?>/"><?php echo $news[0]["username"]; ?></a>
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
								<li class="news__entry any__obscure lazy" data-src="<?php echo $news[$i]["image"]; ?>">
									<h3>
										<a href="/blog/<?php echo $news[$i]["friendly"]; ?>/"><?php echo $news[$i]["title"]; ?></a>
									</h3>
								</li>
							<?php
						}
					?>
				</ul>
				
				<div class="any__obscure news__entry news__vip any--flex">
					<?php
						if($is_vip) {
							?>
								<p>
									<a class="symbol__vip" href="<?php echo '/vip/'.$rslt_vip["friendly"].'/'; ?>">[VIP] <?php echo $rslt_vip["title"]; ?></a>
									<?php echo !$rslt_vip["is_viewed"] ? '<span class="news__new any--weaken-size">NEW</span>' : null; ?>
									<br />
									<a class="symbol__vip" href="/vip/development/">[VIP] Suggestions</a>
								</p>
								<a class="a--padded a--outlined" href="/vip/" style="margin-left: auto;">VIP section</a>
							<?php
						}
						else {
							?>
								<span class="symbol__vip">VIP members can view exclusive updates in this area.</span>
								<a class="symbol__next" href="https://www.patreon.com/vkgy/" target="_blank" style="margin-left: auto; white-space: nowrap;">Support vkgy</a>
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
		<style>
			.support__container::after {
				background: linear-gradient(to right, transparent 728px, var(--background--faint));
				content: "";
				display: block;
				height: 100%;
				left: 0;
				pointer-events: none;
				position: absolute;
				top: 0;
				width: 100%;
				z-index: 2;
			}
			.support__link:first-of-type {
			}
			.support__link {
				display: inline-block;
			}
			.support__image {
				object-fit: contain;
				height: 90px;
				width: 728px;
				max-width: 100%;
				max-height: 100%;
			}
			.comment--unapproved .comment__notice {
				color: var(--accent);
				display: initial !important;
			}
		</style>
		
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
							$comment_class .= ($_SESSION['admin'] ? 'comment--unapproved' : 'any--hidden');
						}
						
						?>
							<li class="obscure__item <?php echo $comment_class; ?>">
								<div class="any--flex">
									<a class="comment__avatar-container <?php echo $comments[$i]["user"]["avatar_class"]; ?>" href="/users/<?php echo $comments[$i]['user']['username']; ?>/">
										<img alt="<?php echo $comments[$i]['user']['username']; ?>'s avatar" class="comment__avatar" src="https://vk.gy/<?php echo $comments[$i]['user']['avatar_url']; ?>" />
									</a>
									
									<div class="comment__comment">
										<h5 class="any--flex">
											<a class="user a--inherit comment__user" href="/users/<?php echo $comments[$i]["user"]["username"]; ?>/"><?php echo $comments[$i]["user"]["username"]; ?></a>
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
			<div class="text text--outlined">
				<ul>
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
							<input class="any--flex-grow" name="register_username" pattern="[A-z0-9-]+" placeholder="username (ユーザー名)" title="A-z, 0-9, -" />
						</div>
						
						<div class="any--weaken register__note">
							Usernames may contain: <strong>A-z</strong>, <strong>0-9</strong>, <strong>-</strong>. <span class="any--jp">（半角英字、数字、ハイフンを使用できます。）</span>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label"><?php echo lang('Password', 'パスワード', ['secondary_class' => 'any--hidden']); ?></label>
								<input class="any--flex-grow symbol__locked" name="register_password" placeholder="password (パスワード)" type="password" />
							</div>
							<div class="input__group">
								<input class="register__show any--hidden" id="register__show" type="checkbox" />
								<label class="input__checkbox-label symbol__unchecked register__show-label" onclick="togglePassword()" for="register__show">Show?</label>
							</div>
						</div>
					</li>
					
					<li class="register__avatar-container">
						<div class="input__row">
							<div class="input__group" style="align-self: center;">
								<label class="input__label">Avatar eyes</label>
								<label class="input__checkbox-label symbol__unchecked register__bat" for="register__radio--bat">bat</label>
								<label class="input__checkbox-label symbol__unchecked register__gecko" for="register__radio--gecko">gecko</label>
							</div>
							<div class="input__group">
								<div class="register__face"></div>
							</div>
						</div>
						
						<div class="any--weaken register__note">
							The avatar can be further customized after joining. <span class="any--jp">（登録後、アバターをさらにカスタマイズすることができます。）</span>
						</div>
					</li>
					
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow" data-role="submit-container">
								<button class="any--flex-grow" data-role="submit" name="register_submit" type="submit">Join vkgy (登録する)</button>
								<span class="register__status" data-role="status"></span>
							</div>
						</div>
					</li>
				</ul>
				
				<div class="text text--outlined text--notice register__result" data-role="result"></div>
			</form>
			
			<div class="register__section">
				<h3>
					Why join?
				</h3>
				<div class="text text--outlined">
				<ul class="ul--bulleted">
					<li>Post, comment, review</li>
					<li>Tag artists/releases</li>
					<li>Customize your avatar</li>
					<li>Show off your collection</li>
					<li>Tag your music files</li>
					<li>Exclusives for VIP supporters</li>
				</ul>
				</div>
				
				<h3>
					Already a member?
				</h3>
				<form action="/account/function-sign_in.php" class="text text--outlined sign-in__container sign-in--refresh" enctype="multipart/form-data" method="post" name="form__main-signin">
					<div class="input__row">
						<div class="input__group">
							<label class="input__label">Sign in</label>
							<input class="any--flex-grow input" name="username" placeholder="username" autocomplete="username" />
							<input class="any--flex-grow input--secondary symbol__locked" name="password" placeholder="password" autocomplete="current-password" type="password" />
						</div>
						<div class="input__group" data-role="submit-container">
							<button class="any--flex-grow" data-role="submit">Sign in</button>
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

<div class="col c2 any--margin section__support">
	<div class="">
		<h1>
			<?php echo lang('Patreon supporters', 'パトレオン', ['primary_container' => 'div', 'secondary_container' => 'div']); ?>
		</h1>
		
		<div class="text text--notice any--weaken-color">
			<ul class="ul--inline support__list">
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
	
	<div class="support__why any--flex">
		<div class="text text--outlined support__text">
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
		<h3>
			Browse artist tags
		</h3>
		<div class="text text--outlined">
			<?php
				foreach($rslt_artist_tags as $tag) {
					echo '<a class="symbol__tag" href="/search/artists/?tags[]='.$tag["friendly"].'#result">'.($tag["romaji"] ?: $tag["name"]).'</a> ('.$tag["num_tagged"].') &nbsp; ';
				}
			?>
		</div>
	</div>
	<div>
		<h3>
			Browse release tags
		</h3>
		<div class="text text--outlined">
			<?php
				foreach($rslt_release_tags as $tag) {
					echo '<a class="symbol__tag" href="/search/releases/?tag='.$tag["friendly"].'#result">'.($tag["romaji"] ?: $tag["name"]).'</a> ('.$tag["num_tagged"].') &nbsp; ';
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
				<li><a href="mailto:inartistic@gmail.com">Email founder</a></li>
				<li><a href="https://twitter.com/vkgy_" target="_blank">Message on Twitter</a></li>
				<li><a href="https://facebook.com/vkgyofficial" target="_blank">Message on Facebook</a></li>
			</ul>
		</div>
	</div>
</div>