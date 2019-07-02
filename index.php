<?php
	if($_SERVER["SERVER_NAME"] === "vk.gy") {
		$vkgy = true;
	}
	
	error_reporting(E_ALL & ~E_NOTICE);
	chdir("php");
	
	$error_path = "../errors/".str_replace ("/", "|", $_SERVER ["REQUEST_URI"].$_SERVER["PATH_INFO"].$_SERVER["QUERY_STRING"])."-".$_SERVER["REQUEST_METHOD"]."-".$_SERVER["REMOTE_ADDR"];
	@ini_set ("error_log", $error_path);
	
	include_once("php/include.php");
	
	$functions = [
		"url",
		"script",
		"style",
		"background",
		"breadcrumbs",
		'page_header',
		"subnav",
		"image_exists",
		"update_views"
	];
	
	foreach($functions as $function) {
		$function = "../php/".(strpos($function, "-") === false ? "function-" : "").$function.".php";
		include_once($function);
	}
	
	$login = new login($pdo);
	$login->check_login();
	
	script([
		"/scripts/external/script-jquery-3.2.1.js",
		"/scripts/external/script-lazyload.js",
		'/scripts/script-lazyLoadYouTube.js',
		"/scripts/script-inlineSubmit.js",
		"/scripts/script-initLazyLoad.js",
		"/scripts/external/script-popper.js",
		"/scripts/external/script-tippy.js",
		"/scripts/script-tooltips.js",
		"/scripts/script-topButton.js",
		'/scripts/script-showObscured.js',
		'/scripts/script-watchNav.js',
		'/scripts/script-showSearch.js',
		'/scripts/script-switchLang.js',
	]);
	
	ob_start();
	include($requested_page);
	$page_contents = ob_get_contents();
	ob_end_clean();
	
	if($_SESSION['username'] === 'inartistic') {

		$access_badge = new access_badge($pdo);
		$badge_notification = $access_badge->notify_if_new_badge();

		if($badge_notification) {
			$page_contents = $badge_notification.$page_contents;
		}
	}
	
	include("../php/index.php");
	
	$pdo = null;
?>