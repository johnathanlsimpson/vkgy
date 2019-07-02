<?php

$artist_header_link = '<a class="artist symbol__artist a--inherit" href="/artists/'.$artist['friendly'].'/">{name}</a><div class="any--weaken">{secondary}</div>';

$artist_header_en = str_replace(
	['{name}', '{secondary}'],
	[($artist['romaji'] ?: $artist['name']), ($artist['romaji'] ? $artist['name'] : null)],
	$artist_header_link
);

$artist_header_jp = str_replace(
	['{name}', '{secondary}'],
	[$artist['name'], ($artist['pronunciation'] && $artist['pronunciation'] != $artist['name'] ? $artist['pronunciation'] : null)],
	$artist_header_link
);

$page_header = lang($artist_header_en, $artist_header_jp, [ 'secondary_class' => 'any--hidden' ]);

subnav([
	'Profile' => '/artists/'.$artist['friendly'].'/',
	'Videos' => '/artists/'.$artist['friendly'].'/videos/',
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