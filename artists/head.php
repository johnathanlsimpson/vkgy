<?php

$page_header = lang(($artist['romaji'] ?: $artist['name']), $artist['name'], [ 'container' => 'div' ]);

subnav([
	'Profile' => '/artists/'.$artist['friendly'].'/',
	'Releases' => '/releases/'.$artist['friendly'].'/',
	'News' => '/blog/artist/'.$artist['friendly'].'/',
]);

subnav([
	'Edit artist' => '/artists/'.$artist['friendly'].'/edit/',
], 'interact', true);

// Set/unset default image
$default_image = "/artists/".$artist["friendly"]."/main.large.jpg";
if(image_exists($default_image, $pdo)) {
	background($default_image);
}
else {
	unset($default_image);
}