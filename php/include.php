<?php
	$vkgy = true;
	
	error_reporting(E_ALL & ~E_NOTICE);
	
	session_start();
	
	include_once('../php/database-connect.php');
	include_once('../php/function-sanitize.php');
	include_once('../php/function-friendly.php');
	include_once('../php/class-login.php');
	include_once('../php/class-access_artist.php');
	include_once('../php/class-access_badge.php');
	include_once('../php/class-access_blog.php');
	include_once('../php/class-access_comment.php');
	include_once('../php/class-access_image.php');
	include_once('../php/class-access_label.php');
	include_once('../php/class-access_live.php');
	include_once('../php/class-access_musician.php');
	include_once('../php/class-access_points.php');
	include_once('../php/class-access_release.php');
	include_once('../php/class-access_user.php');
	include_once('../php/class-access_badge.php');
	include_once('../php/class-parse_markdown.php');
	include_once('../php/function-parse_edit_history.php');
	include_once('../php/function-image_exists.php');
	include_once('../php/function-lang.php');
	include_once('../translations/function-translate.php');
	
	//echo 'before login class <pre>'.print_r($_SESSION, true).'</pre>';
	
	// Init login class, and check status/roles
	$login = new login($pdo);
	
	if($_SESSION['is_signed_in']) {
		$login->set_roles( $login->check_roles( $_SESSION['user_id'] ) );
	}
	else {
		$login->check_login();
	}
	
	//echo 'after login class <pre>'.print_r($_SESSION, true).'</pre>';
?>