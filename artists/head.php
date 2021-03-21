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
	'Images' => '/artists/'.$artist['friendly'].'/images/',
	'Videos' => '/artists/'.$artist['friendly'].'/videos/',
	'Releases' => '/releases/'.$artist['friendly'].'/',
	'News' => '/blog/artist/'.$artist['friendly'].'/',
	'Tags' => '/artists/'.$artist['friendly'].'/tags/',
]);

subnav([
	'Edit artist' => '/artists/'.$artist['friendly'].'/edit/',
], 'interact', true);

style([
	'/artists/style-head.css',
]);

// Set/unset default image
$default_image = "/artists/".$artist["friendly"]."/main.large.jpg";
if(image_exists($default_image, $pdo)) {
	background($default_image);
}
else {
	unset($default_image);
}

include_once('../lists/function-render_lists.php');

// If don't have activity status, get it
if( !is_numeric($artist['active']) ) {

	$artist = $artist + $access_artist->access_artist([ 'id' => $artist['id'], 'get' => 'basics' ]);

}

// Get appropriate name of band's activity status
$activity_statuses = [
	[ 'unknown',     '不明' ],
	[ 'active',      '現在活動' ],
	[ 'disbanded',   '解散' ],
	[ 'paused',      '休止' ],
	[ 'semi-active', '時々活動' ],
];

$activity_romaji = $activity_statuses[ $artist['active'] ][0];
$activity_name = $activity_statuses[ $artist['active'] ][1];

// Add status, website, and list button in header
$GLOBALS['page_header_supplement'] = 
	'<a class="artist__status a--padded artist--'.$activity_romaji.'" href="/search/artists/&active='.$artist['active'].'#result">'.lang( $activity_romaji, $activity_name, 'hidden' ).'</a>'.
	( $artist_website ? '<a class="artist__website a--padded symbol__arrow-right-circled" href="'.$artist_website.'" target="_blank">official</a>' : null ).
	render_lists_dropdown([ 'item_id' => $artist['id'], 'item_type' => 'artist' ]);