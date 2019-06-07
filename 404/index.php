<?php
$request = urldecode(substr($_SERVER["REQUEST_URI"], 1));
$request = str_replace(['/', '-'], ' ', $request);
$request = trim($request);
$request = $_SERVER['REQUEST_URI'] === '/404/' && $request === '404' ? null : $request;

$page_title = 'Page not found (エラー)';

$error = 'Sorry, the requested page couldn\'t be found. '.($request ? 'Here\'s a search for &ldquo;'.sanitize($request).'&rdquo;.' : 'Try a search instead.');

if(strlen($request)) {
	$search['q'] = $request;
}

include('../search/index.php');