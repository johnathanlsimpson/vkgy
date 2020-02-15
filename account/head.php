<?php

breadcrumbs([
	'Users' => '/users/',
]);

subnav([
	lang('Member list', 'メンバー一覧', ['secondary_class' => 'any--hidden']) => '/users/',
], 'interact');

if($_SESSION['is_signed_in']) {
	subnav([
		lang('My profile', 'マイページ', 'hidden') => '/users/'.$_SESSION['username'].'/',
		lang('Sign out', 'サインアウト', 'hidden') => '/sign-out/',
	], 'interact');
}
else {
	subnav([
		lang('Register/Sign in', '登録・サインイン', 'hidden') => '/account/',
	], 'interact');
}