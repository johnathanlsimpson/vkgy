<?php
	$vkgy = true;
	
	error_reporting(E_ALL & ~E_NOTICE);
	
	session_start();
	
	include_once("../php/database-connect.php");
	include_once("../php/function-sanitize.php");
	include_once("../php/function-friendly.php");
	include_once("../php/class-login.php");
	include_once("../php/class-access_artist.php");
	include_once("../php/class-access_blog.php");
	include_once("../php/class-access_comment.php");
	include_once('../php/class-access_image.php');
	include_once("../php/class-access_label.php");
	include_once("../php/class-access_live.php");
	include_once("../php/class-access_musician.php");
	include_once("../php/class-access_release.php");
	include_once("../php/class-access_user.php");
	include_once('../php/class-access_badge.php');
	include_once("../php/class-parse_markdown.php");
	include_once("../php/function-parse_edit_history.php");
	include_once("../php/function-image_exists.php");
	include_once("../php/function-lang.php");
	
	if($_SESSION['is_signed_in']) {
		$sql_check_status = 'SELECT rank, is_vip FROM users WHERE id=? LIMIT 1';
		$stmt_check_status = $pdo->prepare($sql_check_status);
		$stmt_check_status->execute([ $_SESSION['user_id'] ]);
		$rslt_check_status = $stmt_check_status->fetch();
		
		$_SESSION['admin'] = $rslt_check_status['rank'];
		$_SESSION['is_admin'] = $rslt_check_status['rank'];
		$_SESSION['is_vip'] = $rslt_check_status['is_vip'];
	}
	else {
		$login = new login($pdo);
		$login->check_login();
	}
?>