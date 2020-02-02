<?php

subnav([
	lang('Profile', 'プロフィール', 'hidden') => '/users/'.$user['username'].'/',
	lang('Activity', '活動', 'hidden') => '/users/'.$user['username'].'/activity/',
]);

if($_SESSION['username'] === $user['username']) {
	subnav([
		lang('Edit settings', '情報変更', 'hidden') => '/account/',
		lang('Edit avatar', 'アバター変更', 'hidden') => '/account/edit-avatar/',
	]);
}