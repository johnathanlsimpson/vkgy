<?php

$request = urldecode(substr($_SERVER["REQUEST_URI"], 1));
$request = str_replace(['/', '-'], ' ', $request);
$request = trim($request);
$request = $_SERVER['REQUEST_URI'] === '/404/' && $request === '404' ? null : $request;

$page_title = tr('Page not found');

$error =
	tr('Sorry, the requested page couldn\'t be found.').' '.
	($request ? tr('Here\'s a search for &ldquo;{request}&rdquo;.', [ 'replace' => [ 'request' => sanitize($request) ] ]) : tr('Try a search instead.') );

if(strlen($request)) {
	$search['q'] = $request;
}

include('../search/index.php');