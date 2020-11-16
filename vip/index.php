<?php

$access_user = new access_user($pdo);
$access_comment = new access_comment($pdo);
$markdown_parser = new parse_markdown($pdo);
	
breadcrumbs([
	'VIP membership' => '/vip/',
]);
	
subnav([
	'VIP membership' => '/vip/',
	'Become VIP' => 'https://patreon.com/vkgy',
]);

$page_header = 'VIP membership';

include('../vip/page-index.php');