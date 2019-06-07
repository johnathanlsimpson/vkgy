<?php

breadcrumbs([
	"Account services" => "/account/"
]);

if($_SESSION['loggedIn']) {
	subnav([
		lang('My account', 'マイアカウント', ['secondary_class' => 'any--hidden']) => '/users/'.$_SESSION['username'].'/',
	]);
}
else {
	subnav([
		lang('Register/Sign in', '登録・サインイン', ['secondary_class' => 'any--hidden']) => '/account/',
	]);
}
subnav([
	lang('Member list', 'メンバー一覧', ['secondary_class' => 'any--hidden']) => '/users/',
	lang('Documentation', 'ガイド', ['secondary_class' => 'any--hidden']) => '/documentation/',
]);
if($_SESSION['loggedIn']) {
	subnav([
		lang('Sign out', 'サインアウト', ['secondary_class' => 'any--hidden']) => '/sign-out/',
	]);
}