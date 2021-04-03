<?php

// Fallback title
$page_title = 'Musicians | メンバー';

breadcrumbs([
	lang('Musicians', 'メンバー', 'hidden') => '/musicians/'
]);

subnav([
	lang('Find a musician', 'ミュージシャンの検索', 'hidden') => '/musicians/'
]);

if( $_SESSION['can_add_data'] ) {
	subnav([
		lang('Add musicians', 'ミュージシャン追加', 'hidden') => '/musicians/add/',
	], 'interact', true);
}