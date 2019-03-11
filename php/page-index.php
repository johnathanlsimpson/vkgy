<?php
	$body_class = $_SESSION["loggedIn"] ? "body--signed-in" : "body--signed-out";
	$page_description = $page_description ? $page_description." | vkgy (ブイケージ)" : "vkgy is a visual kei library maintained by overseas fans. vkgy（ブイケージ）はビジュアル系のファンサイトとライブラリです。関連するアーティストのメンバープロフィールや活動やリリース情報などがあります。";
	$page_title = $pageTitle ? $pageTitle." | vkgy (ブイケージ)" : "vkgy (ブイケージ) | visual kei library (ビジュアル系のファンサイトとライブラリ)";
	$page_image = $page_image ?: "https://vk.gy/support/patreon-back.png";
	$background_image = $background_image ?: ($page_image ?: null);
	
	style([
		"../style/style-tooltips.css"
	]);
?>
<!doctype html>
<html>
	<head>
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-5KZPGP8');</script>
		
		<title><?php echo $page_title; ?></title>
		
		<link rel="stylesheet" id="stylesheet_theme" href="/style/style-colors<?php echo is_numeric($_SESSION['site-theme']) && $_SESSION['site-theme'] > 0 ? '-'.$_SESSION['site-theme'] : null; ?>.css" />
		<link rel="stylesheet" href="/style/style-critical.css<?php echo '?'.date('Ymd'); ?>" />
		<?php
			if(is_array($GLOBALS["styles"])) {
				array_unshift($GLOBALS["styles"], "/style/style-shared.css");
			}
			else {
				$GLOBALS["styles"] = ["/style/style-shared.css"];
			}
		?>
		
		<link rel="alternate" href="https://vk.gy/rss/" title="RSS feed | vk.gy (ブイケージ)" type="application/rss+xml" />
		
		<meta charset="utf-8" />
		<meta content="initial-scale=1, width=device-width" name="viewport" />
		<meta content="<?php echo $page_description; ?>" name="description" />
		<meta content="<?php echo $page_title; ?>" name="title" />
		
		<meta property="og:site_name" content="vk.gy (ブイケージ)" />
		<meta property="og:description" content="<?php echo $page_description; ?>" />
		<meta property="og:title" content="<?php echo str_replace(" | vkgy (ブイケージ)", "", $page_title); ?>" />
		<meta property="og:image" content="<?php echo $page_image; ?>" />
		<meta name="twitter:site" content="@vkgy_" />
		<meta name="twitter:creator" content="@<?php echo $page_creator ?: "vkgy_"; ?>" />
		<meta name="twitter:card" content="summary_large_image" />
		
		<link rel="apple-touch-icon" sizes="180x180" href="/style/icons/apple-touch-icon.png?v=kPx5XXPBc2">
		<link rel="icon" type="image/png" sizes="32x32" href="/style/icons/favicon-32x32.png?v=kPx5XXPBc2">
		<link rel="icon" type="image/png" sizes="16x16" href="/style/icons/favicon-16x16.png?v=kPx5XXPBc2">
		<link rel="manifest" href="/style/icons/site.webmanifest?v=kPx5XXPBc2">
		<link rel="mask-icon" href="/style/icons/safari-pinned-tab.svg?v=kPx5XXPBc2" color="#6f1131">
		<link rel="shortcut icon" href="/style/icons/favicon.ico?v=kPx5XXPBc2">
		<meta name="apple-mobile-web-app-title" content="vk.gy">
		<meta name="application-name" content="vk.gy">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="/mstile-310x310.png">
		<meta name="theme-color" content="#6f1131">
		
		<?php display_scripts("top"); ?>
		
		<?php display_styles(); ?>
	</head>
	<body class="<?php echo $body_class; ?>">
		<input class="any--hidden" id="language-en" name="language" type="radio" value="en" <?php echo !isset($_SESSION['site_lang']) || $_SESSION['site_lang'] == 0 ? 'checked' : null; ?> />
		<input class="any--hidden" id="language-ja" name="language" type="radio" value="ja" <?php echo $_SESSION['site_lang'] == 1 ? 'checked' : null; ?> />
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5KZPGP8" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		
		<?php
			$user_link = $_SESSION['username'] ? '/users/'.$_SESSION['username'].'/' : '/account/';
			$avatar_url = '/usericons/avatar-'.$_SESSION['username'].'.png';
			$avatar_url = file_exists('..'.$avatar_url) ? $avatar_url : '/usericons/avatar-anonymous.png';
		?>
		
		<div class="col c1 head head">
			<div class="head__container">
				<div class="head__links any--flex">
					<a href="https://vk.gy/" class="head__item">
						<svg x="0px" y="0px" width="0" height="0" viewBox="0 0 105 164" class="head__cage" fill="none" stroke="var(--accent)" stroke-width="5">
							<path d="M52.5,161.5c-27.6,0-50-8.3-50-16v-88c0-24.4,17.6-44.7,40.8-49c1.5-3.5,5.1-6,9.2-6c4.1,0,7.6,2.5,9.2,6 c23.3,4.3,40.9,24.6,40.9,49v88C102.5,153.2,80.1,161.5,52.5,161.5z" /><path d="M42.5,107.7c0,4.6,0,8.8,0,12.2 M52.5,7.5c-5.5,0-10,13.9-10,41" /><path d="M22.5,101.5c0,16.1,0,56.1,0,56.1 M52.5,7.5c-16.6,0-30,20.9-30,48" /><path d="M62.5,107.9c0,16.3,0,30.5,0,37.7 M52.5,7.5c5.5,0,10,13.9,10,41" /><path d="M82.5,136.5L82.5,136.5 M82.5,100.6c0,8.9,0,17.9,0,26.2 M52.5,7.5c16.6,0,30,20.9,30,48" /><path d="M52.5,108.1c-22.4,0-41.6-12.3-50-30c8.4-17.7,27.6-30,50-30c22.4,0,41.6,12.3,50,30C94.1,95.7,74.8,108.1,52.5,108.1z" /><path d="M82.5,57.5 c0,16.7-13.3,30.3-30,30.3s-30-13.5-30-30.3" />
						</svg>
					</a>
					<?php
						$links = [
							["news",   "ニュース", "/blog/",     "symbol__news"],
							["bands",  "バンド",   "/artists/",  "symbol__artist"],
							["music",  "リリース", "/releases/", "symbol__release"],
							["more",   "もっと",   "/database/", "symbol__database"],
							["search", "サーチ",   "/search/",   "symbol__search"],
						];
						foreach($links as $link) {
							?>
								<a class="head__item head__nav a--inherit" href="<?php echo $link[2]; ?>"><?php echo lang($link[0], $link[1], ['secondary_container' => 'div', 'secondary_class' => 'any--weaken']); ?></a>
							<?php
						}
					?>
				</div>
				
				<input class="head__search" form="form__search" name="q" placeholder="search" size="6" />
				<span class="any--weaken-color head__symbol symbol--standalone symbol__search"></span>
				
				<div class="head__right">
					<a class="head__item head__nav a--inherit head__vip" href="/vip/"><?php echo lang('VIP', 'ビップ', ['secondary_container' => 'div', 'secondary_class' => 'any--weaken']); ?></a>
					<a class="head__item head__user-link" href="<?php echo $user_link; ?>">
						<img alt="<?php echo $_SESSION['username']; ?>" class="head__avatar" src="<?php echo $avatar_url; ?>" />
					</a>
					<button class="head__button" form="form__search" name="submit" type="submit"><?php echo lang('search', 'サーチ', ['primary_container' => 'span', 'secondary_container' => 'span', 'secondary_class' => 'any--hidden']); ?></button>
				</div>
			</div>
		</div>
		
		<form action="/search/" class="any--hidden" enctype="multipart/form-data" id="form__search" method="get" name="form__search"><button type="submit"></button></form>
		<div class="head__spacer">&nbsp;</div>
		
		<div class="underhead__wrapper col c1 any--margin any--weaken-color">
			<div class="underhead__container any--flex">
				<div class="underhead__account">
					<a class="a--inherit any--signed-out-only head__item symbol__help" href="/account/"><?php echo lang('account', 'アカウント', ['secondary_class' => 'any--hidden']); ?></a>
					<a class="a--inherit any--signed-in-only head__item head__signout symbol__exit" href="/sign-out/&request=<?php echo $_SERVER["REQUEST_URI"]; ?>"><?php echo lang('sign out', 'サインアウトする', ['secondary_class' => 'any--hidden']); ?></a>
					<?php
					if($_SESSION['username'] === 'inartistic') { ?> 
					<a class="a--inherit any--signed-in-only head__item head__user user" href="/users/<?php echo $_SERVER["username"]; ?>/"><?php echo $_SESSION['username']; ?></a>
					<?php } ?>
					
					<?php
						if($_SESSION['loggedIn']) {
							$sql_vip = 'SELECT 1 FROM users WHERE id=? AND is_vip=? LIMIT 1';
							$stmt_vip = $pdo->prepare($sql_vip);
							$stmt_vip->execute([ $_SESSION['userID'], 1 ]);
							$rslt_vip = $stmt_vip->fetchColumn();
						}
						
						if($rslt_vip) {
							?>
								<label class="input__checkbox-label symbol__unchecked" for="language-en">EN</label>
								<label class="input__checkbox-label symbol__unchecked" for="language-ja"><?php echo sanitize('日本語'); ?>&beta;</label>
							<?php
						}
					?>
				</div>
				
				<script>
					let enButton = document.querySelector('[for="language-en"]');
					let jaButton = document.querySelector('[for="language-ja"]');
					
					function setLang(event) {
						let url = '/php/function-set_lang.php';
						let lang = 'en';
						let connection = new XMLHttpRequest();
						
						if(event.target.htmlFor === 'language-ja') {
							lang = 'ja';
						}
						
						connection.open('POST', url);
						connection.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
						connection.send("lang=" + lang);
					}
					
					enButton.addEventListener('click', setLang);
					jaButton.addEventListener('click', setLang);
				</script>
				
				<form action="/account/function-sign_in.php" class="underhead__sign-in any--flex any--signed-out-only input__row sign-in__container" enctype="multipart/form-data" method="post" name="form__signin">
					<div class="input__group">
						<input name="username" placeholder="username" />
						<input class="input--secondary" name="password" placeholder="password" type="password" />
						<button name="submit" type="submit">Sign in</button>
						<span class="signin__status" data-role="status" data-status=""></span>
						<span class="signin__status-message" data-role="status-message"></span>
					</div>
				</form>
			</div>
		</div>
		
		<?php
			if($background_image) {
				?>
					<div class="content__background any__obscure any__obscure--fade lazy" data-src="<?php echo $background_image; ?>"></div>
				<?php
			}
			
			if(!empty($breadcrumbs) || !empty($subnavs)) {
				?>
					<div class="col c1 subhead__container">
						<div class="subhead__inner">
							<?php
								if(!empty($breadcrumbs)) {
									?>
										<ol class="breadcrumb__container any--weaken" itemscope itemtype="http://schema.org/BreadcrumbList">
											<?php
												foreach($breadcrumbs as $num => $breadcrumb) {
													?>
														<li class="breadcrumb__item symbol symbol__caret-right" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
															<a class="a--padded a--inherit" href="<?php echo $breadcrumb["url"]; ?>" itemprop="item">
																<span itemprop="name">
																	<?php
																		echo $breadcrumb["text"];
																	?>
																</span>
															</a>
															<meta itemprop="position" content="<?php echo $num + 1; ?>" />
														</li>
													<?php
												}
											?>
										</ol>
									<?php
								}
								if(!empty($subnavs) && is_array($subnavs)) {
									?>
										<div class="subnav__inner">
											<div class="any--weaken subnav">
												<?php
													foreach($subnavs as $subnav_chunk) {
														if(is_array($subnav_chunk) && !empty($subnav_chunk)) {
															foreach($subnav_chunk as $key => $subnav) {
																$class = ($subnav["signed_in_only"] ? "any--signed-in-only" : null)." ".($key === 0 ? "subnav__section" : null);
																$class .= $_SERVER['REQUEST_URI'] === $subnav['url'] ? 'subnav--active' : null;
																?>
																	<a class="a--inherit a--padded <?php echo $class; ?>" href="<?php echo $subnav["url"]; ?>">
																		<?php
																			echo $subnav["text"];
																		?>
																	</a>
																<?php
															}
														}
													}
												?>
											</div>
										</div>
									<?php
								}
							?>
						</div>
					</div>
				<?php
			}
		?>
		
		<div class="content__container">
			<?php echo $page_contents; ?>
		</div>
		
		<div class="footer__container">
			<div class="col c4-ABBC any--margin">
				<ul class="any--weaken footer__left">
					<li>
						<a href="/">vkgy</a>
					</li>
					<li>
						<a href="/blog/">news</a>
					</li>
					<li>
						<a href="/artists/">artists</a>
					</li>
					<li>
						<a href="/labels/">labels</a>
					</li>
					<li>
						<a href="/releases/">releases</a>
					</li>
					<li>
						<a href="/account/">account</a>
					</li>
					<li>
						<a href="/search/">search</a>
					</li>
				</ul>
				
				<div class="any--weaken footer__center">
					<a href="https://vk.gy/">vkgy</a> &copy; John (<a class="user" href="/users/inartistic/">inartistic</a>)
					<div>
						SINCE 2004
					</div>
				</div>
				
				<div class="any--weaken footer__right">
					<a class="a--outlined a--padded footer__top" href="#">&#9650; TOP</a>
					<ul>
						<li>
							<h5>
								Twitter
							</h5>
							<a href="https://twitter.com/vkgy_">@vkgy_</a>
						</li>
						<li>
							<h5>
								YouTube
							</h5>
							<a href="https://www.youtube.com/c/vkgyofficial/">vkgyofficial</a>
						</li>
						<li>
							<h5>
								Facebook
							</h5>
							<a href="https://facebook.com/vkgyofficial/">vkgyofficial</a>
						</li>
						<li>
							<h5>
								RSS
							</h5>
							<a href="/rss/">vk.gy/rss/</a>
						</li>
					</ul>
				</div>
			</div>
			
			<div class="col c1 footer__message any--margin any--align-center">
				<div>
					There must be a wonderful world at the end of this road...
				</div>
			</div>
		</div>
		
		<div class="any--hidden">
			<div id="artistTooltip">
				<div class="h3 h1 symbol__artist"><a class="a--inherit symbol__artist quick-name"></a></div>
				<div class="any--jp any--weaken name"></div>
				
				<div class="any--flex">
					<a class="releases symbol__release">Discog<div class="any--jp any--weaken">音源</div></a>
					<a class="profile symbol__artist">Profile<div class="any--jp any--weaken">プロフ</div></a>
					<a class="edit symbol__edit <?php echo !$_SESSION["admin"] ? "any--hidden" : null; ?>">Edit<div class="any--jp any--weaken">編集</div></a>
					<a class="news symbol__news">News<div class="any--jp any--weaken">ニュース</div></a>
				</div>
			</div>
		</div>
		
		<?php display_scripts("bottom"); ?>
		
		<style>
			<?php
				$sql_vip_users = "SELECT username FROM users WHERE is_vip=? ORDER BY username";
				$stmt_vip_users = $pdo->prepare($sql_vip_users);
				$stmt_vip_users->execute([ "1" ]);
				$rslt_vip_users = $stmt_vip_users->fetchAll();
				
				if(is_array($rslt_vip_users) && !empty($rslt_vip_users)) {
					foreach($rslt_vip_users as $vip_user) {
						echo 'a.user[href="/users/'.$vip_user["username"].'/"]::after,'."\n";
					}
				}
			?>
			.user--vip {
				border-radius: 3px;
				box-shadow: inset 0 0 0 1px;
				content: "VIP";
				font-weight: normal;
				margin-left: 3px;
				padding: 0 2px;
			}
		</style>
		
		<script language="javascript" type="text/javascript">
			var sc_project=7964501;
			var sc_invisible=1;
			var sc_security="59674422";
		</script>
		<script language="javascript" src="//www.statcounter.com/counter/counter.js" type="text/javascript"></script>
		<?php
			include_once("../style/symbols.php");
		?>
	</body>
</html>