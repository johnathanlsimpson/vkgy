<?php

breadcrumbs([
	tr('Users') => '/users/',
]);

subnav([
	tr('Member list', ['ja' => 'メンバー一覧']) => '/users/',
], 'interact');

if($_SESSION['is_signed_in']) {
	subnav([
		tr('My profile', ['ja' => 'マイページ']) => '/users/'.$_SESSION['username'].'/',
		tr('Sign out', ['ja' => 'サインアウト']) => '/sign-out/',
	], 'interact');
}
else {
	subnav([
		tr('Register/Sign in', ['ja' => '登録・サインイン']) => '/account/',
	], 'interact');
}