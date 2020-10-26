<?php

subnav([
	tr('Profile', ['ja' => 'プロフィール']) => '/users/'.$user['username'].'/',
	tr('Activity', ['ja' => '活動']) => '/users/'.$user['username'].'/activity/',
]);

if($_SESSION['is_vip']) {
	subnav([
		tr('Lists', ['ja' => 'リスト']).' (&beta;)' => '/users/'.$user['username'].'/lists/',
	]);
}

if($_SESSION['username'] === $user['username']) {
	subnav([
		tr('Edit settings', ['ja' => '情報変更']) => '/account/',
		tr('Edit avatar', ['ja' => 'アバター変更']) => '/account/edit-avatar/',
	]);
}