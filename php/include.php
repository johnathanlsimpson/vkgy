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
include_once('../php/class-sanitizer.php');
include_once('../php/function-parse_edit_history.php');
include_once('../php/function-image_exists.php');
include_once('../php/function-lang.php');
include_once('../php/function-tracking_link.php');
include_once('../translations/class-translate.php');
include_once('../translations/function-init_translate.php');
include_once('../translations/function-tr.php');

// Init login class
$login = new login($pdo);
$access_user = new access_user($pdo);

// If already signed in, make sure roles and permissions are updated
if($_SESSION['is_signed_in']) {
	$login->set_permissions( $access_user->check_permissions( $_SESSION['user_id'] ) );
}

// If not signed in, check if we can sign you in from a cookie
else {
	$login->check_login();
}