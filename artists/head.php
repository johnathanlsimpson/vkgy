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
	
// Make sure we have artist status and website
$sql_status = 'SELECT artists.active, artists_urls.content AS url FROM artists LEFT JOIN artists_urls ON artists_urls.artist_id=artists.id AND artists_urls.type=? AND artists_urls.is_active=? WHERE artists.id=? LIMIT 1';
$stmt_status = $pdo->prepare($sql_status);
$stmt_status->execute([ 1, 1, $artist['id'] ]);
$rslt_status = $stmt_status->fetch();

$activity_type = access_artist::$activity_types[ $rslt_status['active'] ];
$activity_romaji = $activity_type[0];
$activity_name = $activity_type[1];

$activity_string =
	'<a class="a--outlined a--padded" href="/artists/&active='.$activity_romaji.'">'.
	'<span class="status--'.( $rslt_status['active'] == 1 ? 'active' : ( $rslt_status['active'] == 2 ? 'disbanded' : 'other' ) ).'"></span>'.
	lang( $activity_romaji, $activity_name, 'hidden' ).
	'</a>';

$website_string = $rslt_status['url'] ? '<a class="a--padded" href="'.$rslt_status['url'].'" target="_blank">official website</a>' : null;

$GLOBALS['page_header_supplement'] =
	$activity_string.
	$website_string.
	render_lists_dropdown([ 'item_id' => $artist['id'], 'item_type' => 'artist' ]);

$sql_count = '
SELECT
	*
FROM (
	( SELECT "news" AS item_type, COUNT(DISTINCT blog_artists.blog_id) AS num_items FROM blog_artists WHERE blog_artists.artist_id=? )
	UNION
	( SELECT "videos" AS item_type, COUNT(videos.id) AS num_items FROM videos WHERE videos.artist_id=? )
	UNION
	( SELECT "releases" AS item_type, COUNT(releases.id) AS num_items FROM releases WHERE releases.artist_id=? )
	UNION
	( SELECT "images" AS item_type, COUNT(DISTINCT images_artists.image_id) AS num_items FROM images_artists WHERE images_artists.artist_id=? )
) counts
';
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute([ $artist['id'], $artist['id'], $artist['id'], $artist['id'] ]);
$rslt_count = $stmt_count->fetchAll(PDO::FETCH_KEY_PAIR);

$nav_text['releases'] = 'Releases <span class="any--weaken">('.$rslt_count['releases'].')</span>';
$nav_text['images']   = 'Images <span class="any--weaken">('.$rslt_count['images'].')</span>';
$nav_text['videos']   = 'Videos <span class="any--weaken">('.$rslt_count['videos'].')</span>';
$nav_text['news']     = 'News <span class="any--weaken">('.$rslt_count['news'].')</span>';
	
subnav([
	'Profile' => '/artists/'.$artist['friendly'].'/',
]);

if( $rslt_count['releases'] ) {
	subnav([
		$nav_text['releases'] => '/releases/'.$artist['friendly'].'/',
	]);
}

subnav([
	$nav_text['images']   => '/artists/'.$artist['friendly'].'/images/',
	$nav_text['videos']   => '/artists/'.$artist['friendly'].'/videos/',
	'Tags'                => '/artists/'.$artist['friendly'].'/tags/',
]);
	
	subnav([
		lang('Songs', '曲の一覧', 'hidden') => '/songs/'.$artist['friendly'].'/',
	]);
	
if( $rslt_count['news'] ) {
	subnav([
		$nav_text['news'] => '/blog/artist/'.$artist['friendly'].'/',
	]);
}