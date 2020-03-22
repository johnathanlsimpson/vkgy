<?php
include_once('../php/include.php');

$allowed_langs = ['en', 'ja'];
$lang = array_search($_POST['lang'], $allowed_langs) ?: 0;

$_SESSION['site_lang'] = $lang;
setcookie('site_lang', $lang, time() + 60*60*24*30, '/', 'vk.gy', true, true);

if($_SESSION['is_signed_in']) {
	$sql_user_pref = 'UPDATE users SET site_lang=? WHERE id=? LIMIT 1';
	$stmt_user_pref = $pdo->prepare($sql_user_pref);
	$stmt_user_pref->execute([ $lang, $_SESSION['user_id'] ]);
}