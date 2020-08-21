<?php

$page_header = 'About vkgy';

subnav([
	'About' => '/about/#about',
	'Contact' => '/about/#contact',
	'Privacy Policy' => '/about/#privacy'
]);

$allowed_templates = [
	'about' => 'index',
	'contact' => 'contact',
	'privacy-policy' => 'privacy'
];

if(in_array($_GET['template'], array_keys($allowed_templates))) {
	include('../about/page-index.php');
	//include('../about/page-'.$allowed_templates[$_GET['template']].'.php');
}