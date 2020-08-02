<?php

// Setup
include_once('../php/include.php');
include_once('../php/class-access_social_media.php');
$access_social_media = $access_social_media ?: new access_social_media($pdo);

// Set post type
$post_type = 'blog_post';
if(is_array($_POST['tags']) && !empty($_POST['tags'])) {
	foreach($_POST['tags'] as $tag) {
		if($tag === 'interview') {
			$post_type = 'interview';
		}
	}
}

// Set other vars
$title = sanitize($_POST['title']);
$id = sanitize($_POST['id']);
$artist_id = sanitize($_POST['artist_id']);
$user_id = sanitize($_POST['user_id']);
$contributor_ids = explode(',', sanitize($_POST['contributor_ids']));
$url = sanitize($_POST['url']);

// Set overrides
$override_body = sanitize($_POST['override_body']);
$override_twitter_mentions = sanitize($_POST['override_twitter_mentions']);
$override_twitter_authors = sanitize($_POST['override_twitter_authors']);
//$override_authors = sanitize($_POST['override_authors']);

// Send to SNS builder and get output
$sns_post = $access_social_media->build_post([
	'title'                     => $title,
	'id'                        => $id,
	'artist_id'                 => $artist_id,
	'user_id'                   => $user_id,
	'contributor_ids'           => $contributor_ids,
	'url'                       => $url,
	'override_body'             => $override_body,
	'override_twitter_mentions' => $override_twitter_mentions,
	'override_twitter_authors'  => $override_twitter_authors,
	//'override_authors'          => $override_authors,
], $post_type);

// Return output from social post
$output = is_array($output) ? array_merge($output, $sns_post) : $sns_post;

/*$sns_type             => set by tags
$sns_body             => set by post title
$sns_translations     => set by id
$sns_twitter_mentions => set by featured artist id -> find connected twitters             ┓
$sns_twitter_authors  => set by author id and contributor id -> find connected twitters   ┣ these require query
$sns_authors          => set by author id and contributor id -> find connected usernames  ┛
//$sns_image_id         => set by image id--actually no 'cause that's just an <og:> tag 
$sns_url              => set by url
$sns_overrides[
	sns_body             => overridden by sns_body         ┓
	sns_twitter_mentions => overridden by twitter mentions ┃
	sns_twitter_authors  => overridden by twitter authors  ┣ these just are what they are
	sns_authors          => overridden by authors          ┃
	sns_image_id         => overridden by sns_image        ┛
]

$tags;
$title;
$id;
$artist_id;
$author_id;*/

/*include_once('../blog/function-get_artist_twitters.php');
$access_artist = $access_artist ?: new access_artist($pdo);
$access_social_media = $access_social_media ?: new access_social_media($pdo);
$markdown_parser = $markdown_parser ?: new parse_markdown($pdo);*/

/*// If Twitter/FB author credits specified
$twitter_author = sanitize($_POST['twitter_author']);
$facebook_author = sanitize($_POST['facebook_author']);

// Combine author ID and contributor IDs, remove duplicates, remove site owner
$author_id = sanitize($_POST['author_id']);
$contributor_ids = explode(',', sanitize($_POST['contributor_ids']));
$contributor_ids = is_array($contributor_ids) ? $contributor_ids : [];
$contributor_ids[] = $author_id;
$contributor_ids = array_unique($contributor_ids);
$contributor_ids = array_filter($contributor_ids, function($x) { return $x != 0 && $x != 1; });
$contributor_ids = array_values($contributor_ids);

// Get Twitter usernames of remaining contributors (if overrides not set)
if( !$facebook_author && !$twitter_author && is_array($contributor_ids) && !empty($contributor_ids) ) {
	
	// Get user info
	$sql_author = 'SELECT username, twitter FROM users WHERE '.substr(str_repeat('id=? OR ', count($contributor_ids)), 0, -4).'';
	$stmt_author = $pdo->prepare($sql_author);
	$stmt_author->execute( $contributor_ids );
	$rslt_author = $stmt_author->fetchAll();
	
	// Use username as FB credit, Twitter handle as Twitter credit if possible
	if(is_array($rslt_author) && !empty($rslt_author)) {
		foreach($rslt_author as $author) {
			$facebook_author = $author['username'];
			$twitter_author .= $author['twitter'] ? '@'.$author['twitter'].' ' : $author['username'].' ';
		}
	}
	
}

// If artist specified, get Twitter handles for band and its members
$artist_id = sanitize($_POST['artist_id']);
if(is_numeric($artist_id)) {
	$artist_twitters = get_artist_twitters($artist_id, $pdo, $access_artist);
}

// Set vars
$title = sanitize($_POST['title']);
$friendly = friendly($_POST['friendly']);
$url = 'https://vk.gy/blog/'.$friendly.'/';
$id = sanitize($_POST['id']);
$language = sanitize($_POST['language']);*/

// If English (assumed source version), check for translations
/*if(is_numeric($id) && $language === 0) {
	
	// Check for other translations using this article as a source
	$sql_translations = 'SELECT language, friendly FROM blog WHERE source_id=?';
	$stmt_translations = $pdo->prepare($sql_translations);
	$stmt_translations->execute([ $id ]);
	$rslt_translations = $stmt_translations->fetchAll();
	
	// If any translations, save translation URLs separately
	if(is_array($rslt_translations) && !empty($rslt_translations)) {
		foreach($rslt_translations as $translation) {
			
			// Get language code from number--just kind of making it up here, for now
			$language = [ 'en', 'ja', 'es', 'fr' ][ $translation['language'] ];
			$translations[ $language ] = 'https://vk.gy/blog/'.$translation['friendly'].'/';
			
		}
	}
	
	// Save English translation as well
	$translations['en'] = $url;
	
}*/

// Check if post type manually set
/*$post_type = sanitize($_POST['post_type']) ?: 'blog_post';*/

// Send everything to SNS post builder and see what we get
/*$output['sns_post'] = $access_social_media->build_post([
	'title'            => $title,
	'url'              => $url,
	'translations'     => $translations,
	'twitter_mentions' => $artist_twitters,
	'twitter_author'   => $twitter_author,
	'facebook_author'  => $facebook_author
], $post_type ?: 'blog_post');
$output[] = $post_type;
$output[] = $_POST;*/

$output['status'] = 'success';
echo json_encode($output);