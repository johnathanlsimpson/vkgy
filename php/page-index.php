<?php

include_once('../php/function-choose_page_images.php');

// Default title
$page_title = $page_title ?: $pageTitle;
$page_title = $page_title ? $page_title.' | vkgy (ブイケージ)' : 'vkgy (ブイケージ) | visual kei library (V系ライブラリ)';

// Default description
$page_description = $page_description ? $page_description." | vkgy (ブイケージ)" : "vkgy is a visual kei library maintained by overseas fans. vkgy（ブイケージ）はビジュアル系のファンサイトとライブラリです。関連するアーティストのメンバープロフィールや活動やリリース情報などがあります。";

// Body class
$body_class = $_SESSION["is_signed_in"] ? "body--signed-in" : "body--signed-out";

// Set critical page styles
style([
	'/style/style-colors-'.( is_numeric($_SESSION['site_theme']) ? $_SESSION['site_theme'] : 'default' ).'.css',
	'/style/style-critical.css',
	'/style/style-shared.css',
	'/style/style-symbols.css',
	'/style/external/style-simplebar.css',
], 'top');

// Set other page styles
style([
	'/style/style-tooltips.css',
	'/style/style-point.css',
]);

// Set scripts
script([
	'/scripts/external/script-debounceX.js',
	'/scripts/external/script-simplebar.js',
	'/scripts/script-switchLanguage.js',
	'/scripts/script-quickSearch.js',
], 'top');

?>
<!doctype html>
<html>
	<head>
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-5KZPGP8');</script>
		
		<title><?= $page_title; ?></title>
		
		<!-- Display critical styles -->
		<?= display_styles( 'top', true, false ); ?>
		
		<!-- Fonts -->
		<link rel="preconnect" href="https://fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
		
		<!-- RSS -->
		<link rel="alternate" href="https://vk.gy/rss/" title="RSS feed | vk.gy (ブイケージ)" type="application/rss+xml" />
		
		<meta charset="utf-8" />
		<meta content="initial-scale=1, width=device-width" name="viewport" />
		<meta content="<?= $page_description; ?>" name="description" />
		<meta content="<?= $page_title; ?>" name="title" />
		
		<meta property="og:site_name" content="vk.gy (ブイケージ)" />
		<meta property="og:description" content="<?php echo $page_description; ?>" />
		<meta property="og:title" content="<?php echo str_replace(" | vkgy (ブイケージ)", "", $page_title); ?>" />
		<meta property="og:image" content="<?= $page_image; ?>" />
		<meta property="og:url" content="<?= $page_url ?: 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" />
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
		
		<?php display_scripts( 'top' ); ?>
		
		<!-- Other styles -->
		<?= display_styles( 'bottom' ); ?>
		
		<?php if( strpos( $_SERVER['REQUEST_URI'], '/blog/' ) !== 0 ): ?>
		<?= !$_SESSION['is_vip'] || $_SESSION['username'] === 'inartistic' ? '<script data-ad-client="ca-pub-5797371558296978" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' : null; ?>
		<?php endif; ?>
	</head>
	<body class="<?= $body_class; ?>">
		
		<input class="any--hidden" id="language-en" name="language" type="radio" value="en" <?= !isset($translate->language) || $translate->language === 'en' ? 'checked' : null; ?> />
		<input class="any--hidden" id="language-ja" name="language" type="radio" value="ja" <?= $translate->language === 'ja' ? 'checked' : null; ?> />
		
		<!-- Google Tag manager -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5KZPGP8" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		
		<?php
			$user_link = $_SESSION['username'] ? '/users/'.$_SESSION['username'].'/' : '/account/';
			$avatar_url  = '/usericons/avatar-'.$_SESSION['username'].'.png';
			$avatar_url  = file_exists('..'.$avatar_url) ? $avatar_url : '/usericons/avatar-anonymous.png';
			$avatar_url .= '?'.date( 'YmdHis', filemtime('..'.$avatar_url) );
		?>
		
		<img class="any--hidden" src="/style/sprite-cage.png" />
		
		<nav class="col c1 secondary-nav__wrapper">
			<div class="flex any--weaken-color">
				<a class="secondary-nav__home" href="/"></a>
				
				<span class="language__container secondary-nav__lang">
					<div class="language__switch">
						<span class="language__symbol symbol__language symbol--standalone"></span>
						<span class="language__current"><?= ($translate->language_name ?: 'English').($translate->language_name != 'English' ? ' (&beta;)' : null); ?></span>
						<span class="language__caret symbol__caret symbol--down symbol--standalone"></span>
					</div>
					
					<div class="language__dropdown">
						<?php
							foreach($translate->allowed_languages as $language_key => $language_name) {
								echo 
									'<a class="language__choice a--inherit" data-language="'.$language_key.'" href="/translations/function-switch_language.php?refresh=1&language='.$language_key.'">'.
									sanitize($language_name).
									'</a>';
							}
						?>
						<?php if($_SESSION['can_add_data']) { ?><a class="language__help symbol__plus" href="/translations/">translate</a><?php } ?>
					</div>
				</span>
				
				<?php
					$promo_links = [
						['Grab some {{vkgy merch}}'    => 'https://vkgy.myshopify.com'],
						['Support vkgy on {{Patreon}}' => 'https://patreon.com/vkgy'],
						['Join our {{Discord}}'        => 'https://discord.gg/jw8jzXn']
					];
					shuffle($promo_links);
					foreach($promo_links[0] as $promo_link => $promo_url) {
						echo '<a class="secondary-nav__hi" href="'.$promo_url.'" target="_blank">'.str_replace(['{{','}}'], ['<span class="show">','</span>'], $promo_link).'</a>';
						break;
					}
				?>
				
				<a class="head__link secondary-nav__link secondary-nav__sign-out a--inherit symbol__exit     any--signed-in-only"  href="/sign-out/"><?= lang('Sign out', 'サインアウト', 'hidden'); ?></a>
				<a class="head__link secondary-nav__link secondary-nav__register a--inherit symbol__register any--signed-out-only" href="/account/"><?= lang('Register', 'アカウントの作成', 'hidden'); ?></a>
				<a class="head__link secondary-nav__link secondary-nav__sign-in  a--inherit symbol__sign-in  any--signed-out-only" href="/account/"><?= lang('Sign in', 'サインイン', 'hidden'); ?></a>
			</div>
		</nav>
		
		<nav class="col c1 primary-nav__wrapper">
			<div class="primary-nav__container any--flex">
				
				<!-- Home logo -->
				<a href="/" class="primary-nav__home"></a>
				
				<!-- Main nav links -->
				<div class="primary-nav__links any--flex" data-simplebar data-simplebar-force-visible="true">
					<?php
						$nav_links = [
							'news' => '/blog/',
							'bands' => '/artists/',
							'music' => '/releases/',
							'more' => '/database/',
							'search' => '/search/'
						];
						foreach($nav_links as $name => $url) {
							echo '<a class="head__link primary-nav__link a--inherit" href="'.$url.'">'.tr($name, [ 'folder' => 'php', 'lang' => true, 'lang_args' => 'div' ]).'</a>';
						}
					?>
				</div>
				
				<!-- Search -->
				<input class="primary-nav__search" form="form__search" name="q" placeholder="search" size="6" />
				<span class="primary-nav__search-symbol any--weaken-color symbol--standalone symbol__search"></span>
				
				<button class="primary-nav__search-button" form="form__search" name="submit" type="submit"><?= lang('search', 'サーチ', ['primary_container' => 'span', 'secondary_container' => 'span', 'secondary_class' => 'any--hidden']); ?></button>
				
				<div class="quick-search__wrapper quick-search--hidden"><div class="quick-search__container symbol__loading"></div></div>
				
				<!-- Quick links -->
				<div class="primary-nav__right any--flex any--weaken-color">
					<a class="head__link primary-nav__add a--inherit any--signed-in-only" href="/blog/add/" title="Add Blog Post"><span class="symbol__news symbol--standalone"></span></a>
					<a class="head__link primary-nav__add a--inherit any--signed-in-only" title="Add Artist" href="/artists/add/"><span class="symbol__artist symbol--standalone"></span></a>
					<a class="head__link primary-nav__add a--inherit any--signed-in-only" title="Add Release" href="/releases/add/<?= strlen($_GET['artist']) ? friendly($_GET['artist']).'/' : null; ?>"><span class="symbol__release symbol--standalone"></span></a>
					<a class="head__link primary-nav__add a--inherit any--signed-in-only" title="Add Musician" href="/musicians/add/"><span class="symbol__musician symbol--standalone"></span></a>
					<a class="head__link primary-nav__add a--inherit any--signed-in-only" title="Add Label" href="/labels/add/"><span class="symbol__company symbol--standalone"></span></a>
					
					<a class="head__link primary-nav__avatar" title="View Your Profile" href="<?= $user_link; ?>">
						<object alt="<?= $_SESSION['username']; ?>" class="symbol__user symbol--standalone" data="<?= $avatar_url; ?>" type="image/png"></object>
						<span class="primary-nav__notification <?= $_SESSION['num_notifications'] ? null : 'any--hidden'; ?>"></span>
					</a>
				</div>
				
			</div>
		</nav>
		
		<form action="/search/" class="any--hidden" enctype="multipart/form-data" id="form__search" method="get" name="form__search"><button type="submit"></button></form>
		
		<!-- HEADER -->
		<div class="header__wrapper col c1 <?= $large_header ? 'header--large' : null; ?> <?= $extra_large_header ? 'header--extra-large' : null; ?> <?= $plain_header ? 'header--plain' : null; ?> " data-orientation="<?= $background_image['orientation']; ?>" style="<?= $background_image['url'] ? '--background-image:url('.$background_image['url'].');--background-thumbnail:url('.$background_image['thumbnail_url'].');' : null; ?>">
			
			<?= $background_image ? '<img class="header__thumbnail" src="'.$background_image['thumbnail_url'].'" />' : null; ?>
			
			<div class="header__container lazy any--flex">
				<div class="header__header" style="flex-grow:1;">
					<h1><?= $GLOBALS['page_header'] ?: ($page_header ?: null); ?></h1>
					
					<div class="header__supplement" style="z-index:1;">
						<?= $GLOBALS['page_header_supplement']; ?>
					</div>
				</div>
				
				<!-- INTERACT NAV -->
				<div class="quaternary-nav__container any--weaken-size"><?php
					if(is_array($interact_nav) && !empty($interact_nav)) {
						foreach($interact_nav as $nav) {
							if(!$nav['signed_in_only'] || ($nav['signed_in_only'] && $_SESSION['is_signed_in'])) {
								$nav['class'] = ($_SERVER['REQUEST_URI'] === $nav['url'] ? 'quaternary-nav--active' : null);
								?>
									<a class="quaternary-nav__link  a--inherit a--padded <?php echo $nav['class']; ?>" href="<?php echo $nav['url']; ?>"><?php echo $nav['text']; ?></a>
								<?php
							}
						}
					}
				?></div>
			</div>
		</div>
		
		<!-- SECTION NAV -->
		<div class="tertiary-nav__wrapper  col">
			<div class="tertiary-nav__container  any--flex"><?php
				if(is_array($section_nav) && !empty($section_nav)) {
					foreach($section_nav as $nav) {
						if(!$nav['signed_in_only'] || ($nav['signed_in_only'] && $_SESSION['is_signed_in'])) {
							$nav['class'] = explode('&', $_SERVER['REQUEST_URI'])[0] === $nav['url'] || $active_page === $nav['url'] ? 'tertiary-nav--active' : null;
							?>
								<a class="tertiary-nav__link  a--inherit a--padded <?php echo $nav['class']; ?>" href="<?php echo $nav['url']; ?>"><?php echo $nav['text']; ?></a>
							<?php
						}
					}
				}
			?></div>
			<div class="quinary-nav__container  any--flex">
				<?php
					if(is_array($directional_nav) && !empty($directional_nav)) {
						foreach($directional_nav as $nav) {
							$nav['class'] = 'quinary-nav__'.$nav['position'];
							$nav['symbol'] = $nav['symbol'] ?: ($nav['position'] === 'left' ? 'previous' : ($nav['position'] === 'right' ? 'next' : 'random'));
							
							if(strlen($nav['url'])) {
								?>
									<a class="<?php echo $nav['class']; ?>" href="<?php echo $nav['url']; ?>">
										<span class="symbol__<?php echo $nav['symbol']; ?>"></span>
										<span class="quinary-nav__text"><?php echo $nav['text']; ?></span>
									</a>
								<?php
							}
							else {
								?>
									<span class="<?php echo $nav['class']; ?> any--weaken-color">
										<span class="quinary-nav__text"><?php echo $nav['text']; ?></span>
									</span>
								<?php
							}
						}
					}
				?>
			</div>
		</div>
		
		<?php echo $page_contents; ?>
		
		<div class="col c1 any--margin">
			<div class="quinary-nav__container  any--flex">
				<?php
					if(is_array($directional_nav) && !empty($directional_nav)) {
						foreach($directional_nav as $nav) {
							$nav['class'] = 'quinary-nav__'.$nav['position'];
							$nav['symbol'] = $nav['symbol'] ?: ($nav['position'] === 'left' ? 'previous' : ($nav['position'] === 'right' ? 'next' : 'random'));
							
							if(strlen($nav['url'])) {
								?>
									<a class="<?php echo $nav['class']; ?>" href="<?php echo $nav['url']; ?>">
										<span class="symbol__<?php echo $nav['symbol']; ?>"></span>
										<span class="quinary-nav__text"><?php echo $nav['text']; ?></span>
									</a>
								<?php
							}
							else {
								?>
									<span class="<?php echo $nav['class']; ?> any--weaken-color">
										<span class="quinary-nav__text"><?php echo $nav['text']; ?></span>
									</span>
								<?php
							}
						}
					}
				?>
			</div>
		</div>
		
		
		<?php if( strpos( $_SERVER['REQUEST_URI'], '/blog/' ) !== 0 ): ?>
			<?php if( !$_SESSION['is_vip'] || $_SESSION['username'] === 'inartistic' ): ?>
			<div class="col c1 any--margin" style="max-width:100%;overflow:hidden;">

				<div class="any--weaken" style="background:hsl(var(--background));border-radius:3px 3px 0 0;line-height:1;padding:0.5rem;width:auto;">
					<span class="h5">AD</span> | 
					<span class=""><a href="https://patreon.com/vkgy" target="_blank">Please consider becoming a VIP member</a> to remove ads and support vkgy.</span>
				</div>
				<?php if($_SESSION['site_theme'] == 1): ?>
					<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<ins class="adsbygoogle"
										style="display:block"
										data-ad-format="autorelaxed"
										data-ad-client="ca-pub-5797371558296978"
										data-ad-slot="8955090786"></ins>
					<script>
										(adsbygoogle = window.adsbygoogle || []).push({});
					</script>
				<?php else: ?>
					<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<ins class="adsbygoogle"
										style="display:block"
										data-ad-format="autorelaxed"
										data-ad-client="ca-pub-5797371558296978"
										data-ad-slot="7921210718"></ins>
					<script>
										(adsbygoogle = window.adsbygoogle || []).push({});
					</script>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
		
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
					<li>
						<a href="/documentation/">documentation</a>
					</li>
					<li>
						<a href="/about/">about</a>
					</li>
					<li>
						<a href="/about/#contact">contact</a>
					</li>
					<li>
						<a href="/about/#privacy">privacy policy</a>
					</li>
				</ul>
				
				<div class="any--weaken footer__center">
					<?php
						if(!empty($breadcrumbs)) {
							?>
								<ol class="breadcrumb__container" itemscope itemtype="http://schema.org/BreadcrumbList">
									<?php
										foreach($breadcrumbs as $num => $breadcrumb) {
											?>
												<li class="breadcrumb__item symbol symbol__caret" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
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
					?>
					
					<a href="https://vk.gy/">vkgy</a> &copy; John (<a class="user" href="/users/inartistic/">inartistic</a>)
					<div>
						SINCE 2004
					</div>
					
					<a class="a--padded a--outlined cta__link a--patreon" href="https://www.patreon.com/vkgy" target="_blank"><img src="/style/logo-patreon.png" style="height: 1rem;" /> <?php echo lang('Support vkgy', 'パトレオン', ['secondary_class' => 'any--hidden']); ?></a>
				</div>
				
				<div class="any--weaken footer__right">
					<a class="a--outlined a--padded footer__top symbol__triangle symbol--up" href="#">TOP</a>
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
						<li>
							<h5>
								Patreon
							</h5>
							<a href="https://www.patreon.com/vkgy" target="_blank">vkgy</a>
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
					<a class="edit symbol__edit <?php echo !$_SESSION["can_add_data"] ? "any--hidden" : null; ?>">Edit<div class="any--jp any--weaken">編集</div></a>
					<a class="news symbol__news">News<div class="any--jp any--weaken">ニュース</div></a>
				</div>
			</div>
		</div>
		
		<?php include('../php/partial-point.php'); ?>
		
		<?php display_scripts("bottom"); ?>
		
		<script language="javascript" type="text/javascript">
			var sc_project=7964501;
			var sc_invisible=1;
			var sc_security="59674422";
		</script>
		<script language="javascript" src="https://www.statcounter.com/counter/counter.js" type="text/javascript" async></script>
		
	</body>
</html>