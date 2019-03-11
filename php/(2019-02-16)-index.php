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
		<!-- Google Tag Manager2 -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-5KZPGP8');</script>
		<!-- End Google Tag Manager -->
		
		<title><?php echo $page_title; ?></title>
		
		<link rel="stylesheet" id="stylesheet_theme" href="/style/style-colors<?php echo is_numeric($_SESSION['site-theme']) && $_SESSION['site-theme'] > 0 ? '-'.$_SESSION['site-theme'] : null; ?>.css" />
		<link rel="stylesheet" href="/style/style-critical.css" />
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
    <!-- Google Tag Manager (noscript) -->
      <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5KZPGP8"
      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
		<div class="col c1 head">
			<div class="head__container">
				<a href="https://vk.gy/" class="head__nav">
					<svg x="0px" y="0px" width="0" height="0" viewBox="0 0 105 164" class="cage">
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-linecap="round" stroke-miterlimit="10" d="M52.5,161.5c-27.6,0-50-8.3-50-16v-88c0-24.4,17.6-44.7,40.8-49c1.5-3.5,5.1-6,9.2-6c4.1,0,7.6,2.5,9.2,6 c23.3,4.3,40.9,24.6,40.9,49v88C102.5,153.2,80.1,161.5,52.5,161.5z"/>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-linecap="round" stroke-miterlimit="10" d="M42.5,107.7c0,4.6,0,8.8,0,12.2 M52.5,7.5c-5.5,0-10,13.9-10,41"/>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-linecap="round" stroke-miterlimit="10" d="M22.5,101.5c0,16.1,0,56.1,0,56.1 M52.5,7.5c-16.6,0-30,20.9-30,48"/>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-linecap="round" stroke-miterlimit="10" d="M62.5,107.9c0,16.3,0,30.5,0,37.7 M52.5,7.5c5.5,0,10,13.9,10,41"/>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-linecap="round" stroke-miterlimit="10" d="M82.5,136.5L82.5,136.5 M82.5,100.6c0,8.9,0,17.9,0,26.2 M52.5,7.5c16.6,0,30,20.9,30,48"/>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-linejoin="round" stroke-miterlimit="10" d="M52.5,108.1c-22.4,0-41.6-12.3-50-30c8.4-17.7,27.6-30,50-30c22.4,0,41.6,12.3,50,30C94.1,95.7,74.8,108.1,52.5,108.1z"/>
						<path fill-rule="evenodd" clip-rule="evenodd" fill="none" stroke="none" stroke-miterlimit="10" d="M82.5,57.5 c0,16.7-13.3,30.3-30,30.3s-30-13.5-30-30.3"/>
					</svg>
				</a>
				<style>
					.cage {
						object-fit: contain;
						height: 2rem;
						width: 1.28rem;
						margin-right: 0.5rem;
					}
					.cage path {
						stroke: var(--accent);
						stroke-width: 5px;
					}
				</style>
				
				<?php
					$links = [
						["news",     "ニュース",     "/blog/",     "symbol__news"],
						["artist",   "アーティスト", "/artists/",  "symbol__artist"],
						["release",  "リリース",     "/releases/", "symbol__release"],
						["more",     "データベース", "/database/", "symbol__database"],
						["account",  "アカウント",   "/account/",  "symbol__join any--signed-out-only"],
						["account",  "アカウント",   "/account/",  "symbol__user any--signed-in-only"],
						["search",   "サーチ",       "/search/",   "symbol__search"],
					];
					
					foreach($links as $link) {
						?>
							<a class="head__item head__nav a--inherit <?php echo $link[3]; ?>" href="<?php echo $link[2]; ?>"><?php echo $link[0]; ?><div class="any--weaken"><?php echo sanitize($link[1]); ?></div></a>
						<?php
					}
				?>
				
				<input class="head__search" form="form__search" name="q" placeholder="search" size="6" />
				<span class="any--weaken-color symbol--standalone symbol__search"></span>
				
				<form action="" class="head__account sign-in__container sign-in--refresh any--flex" enctype="multipart/form-data" method="post" name="form__signin">
					<a class="a--inherit  any--signed-out-only  head__item symbol__join " href="/account/">account</a>
					
					<a class="a--inherit  any--signed-in-only  head__item  head__signout  symbol__exit"                                          href="/sign-out/&request=<?php echo $_SERVER["REQUEST_URI"]; ?>">sign out</a>
					<a class="a--inherit  any--signed-in-only  head__item  head__user     symbol__user" data-get="user_url" data-get-into="href" href="<?php echo $_SESSION["loggedIn"] ? "/users/".$_SESSION["username"]."/" : ""; ?>"><span data-get="username"><?php echo $_SESSION["username"]; ?></span></a>
					
					<div class="any--signed-out-only  head__item  input__group" >
						<input name="username" placeholder="username" />
						<input class="input--secondary" name="password" placeholder="password" type="password" />
						<button class="any--hidden" name="submit" type="submit">Sign in</button>
						<span class="signin__status" data-role="status" data-status=""></span>
						<span class="signin__status-message" data-role="status-message"></span>
					</div>
				</form>
			</div>
		</div>

		<form action="/search/" class="any--hidden" enctype="multipart/form-data" id="form__search" method="get" name="form__search"><button type="submit"></button></form>
		<div class="head__spacer">&nbsp;</div>
		
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