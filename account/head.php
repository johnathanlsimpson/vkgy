<?php

breadcrumbs([
	"Account services" => "/account/"
]);

if($_SESSION['loggedIn']) {
	subnav([
		lang('My profile', 'マイページ', 'hidden') => '/users/'.$_SESSION['username'].'/',
		lang('Edit settings', '情報変更', 'hidden') => '/account/',
		lang('Edit avatar', 'アバター変更', 'hidden') => '/account/edit-avatar/',
	]);
}
else {
	subnav([
		lang('Register/Sign in', '登録・サインイン', ['secondary_class' => 'any--hidden']) => '/account/',
	]);
}
subnav([
	lang('Member list', 'メンバー一覧', ['secondary_class' => 'any--hidden']) => '/users/',
]);
if($_SESSION['loggedIn']) {
	subnav([
		lang('Sign out', 'サインアウト', ['secondary_class' => 'any--hidden']) => '/sign-out/',
	]);
}