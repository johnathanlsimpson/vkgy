<?php

$access_blog = new access_blog($pdo);
$access_artist = new access_artist($pdo);
$access_comment = new access_comment($pdo);
$markdown_parser = new parse_markdown($pdo);

style([
	"/main/style-page-index.css"
]);

script([
	'/main/script-page-index.js'
]);

background("/images/".["470.medium.png", "3131.medium.jpg", "3134.medium.jpg", "3135.medium.jpg", "3136.medium.jpg"][rand(0, 4)]);

breadcrumbs([
	"Home" => "https://vk.gy/"
]);

$access_image = $access_image ?: new access_image($pdo);

/* Get VIP news */
if($_SESSION['is_vip']) {
	$sql_vip = "SELECT vip.title, vip.friendly, vip.date_occurred, vip_views.id AS is_viewed FROM vip LEFT JOIN vip_views ON vip_views.post_id=vip.id AND vip_views.user_id=? ORDER BY vip.date_occurred DESC LIMIT 1";
	$stmt_vip = $pdo->prepare($sql_vip);
	$stmt_vip->execute([ $_SESSION['user_id'] ]);
	$rslt_vip = $stmt_vip->fetch();
}

/* Get news */
$news = $access_blog->access_blog([ "page" => "latest", "get" => "list" ]);
$num_news = count($news);

$news[0]["content"] = $markdown_parser->parse_markdown($news[0]["content"]);
$news[0]["comment_count"] = $access_comment->access_comment([ "id" => $news[0]["id"], "type" => "blog", "get" => "count" ]);
$news[0]["comment_text"] = ($news[0]["comment_count"] ? 'read '.$news[0]["comment_count"].' comment'.($news[0]["comment_count"] !== "1" ? 's' : null) : 'comment on this entry');

for($i=0; $i<$num_news; $i++) {
	$news[$i]["date_occurred"] = substr($news[$i]["date_occurred"], 0, 10);
	$news[$i]["image"] = str_replace(".", ".medium.", $news[$i]["image"]);
}

/* Get comments */
$comments = $access_comment->access_comment(['is_deleted' => 0, "get" => "list", "limit" => 20]);
$num_comments = count($comments);

// Comments: Loop through comments and set up query to get their URLs
$num_comments = count($comments);
for($i=0; $i<$num_comments; $i++) {
	switch($comments[$i]['item_type']) {
		case('blog'):
			$sql_comment[] = "SELECT id AS item_id, CONCAT_WS('/', '', 'blog', friendly, '') AS url FROM blog WHERE id=?";
			break;
		case('release'):
			$sql_comment[] = "SELECT releases.id AS item_id, CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url FROM releases LEFT JOIN artists ON artists.id=releases.artist_id WHERE releases.id=?";
			break;
		case('artist'):
			$sql_comment[] = "SELECT id AS item_id, CONCAT_WS('/', '', 'artists', friendly, '') AS url FROM artists WHERE id=?";
			break;
		case('vip'):
			$sql_comment[] = "SELECT id AS item_id, CONCAT_WS('/', '', 'vip', friendly, '') AS url FROM vip WHERE id=?";
			break;
	}
	
	$values_comment[] = $comments[$i]['item_id'];
}

// Comments: If we have SQL and values for each comment, query the DB
if( is_array($sql_comment) && !empty($sql_comment) && count($sql_comment) === count($values_comment) ) {
	$sql_comment = 'SELECT * FROM ( ('.implode(') UNION (', $sql_comment).') ) urls';
	$stmt_comment = $pdo->prepare($sql_comment);
	$stmt_comment->execute( $values_comment );
	$rslt_comments = $stmt_comment->fetchAll();
	$num_rslt_comments = is_array($rslt_comments) ? count($rslt_comments) : 0;
}

// Comments: If got comment URLs, loop through and apply them
if($num_rslt_comments) {
	
	// Change comment URLs to associative array
	for($i=0; $i<$num_rslt_comments; $i++) {
		$comments_urls[ $rslt_comments[$i]['item_id'] ] = $rslt_comments[$i]['url'];
	}
	
	// Grab appropriate URL for each comment
	for($i=0; $i<$num_comments; $i++) {
		$comments[$i]['url'] = $comments_urls[$comments[$i]['item_id']];
	}
	
}

// Comments: Format comment data
for($i=0; $i<$num_comments; $i++) {
	
	// Comment date
	$comments[$i]["date_occurred"] = substr($comments[$i]["date_occurred"], 0, 10);
	
	// Parse comment content
	$content = trim($comments[$i]["content"]);
	$content = explode("\n", $content)[0];
	$content = $markdown_parser->parse_markdown($content);
	$content = str_replace(["<p>", "</p>"], "", $content);
	$content = trim($content);
	$comments[$i]["content"] = ($comments[$i]["item_type"] === 'vip' && !$_SESSION['is_vip'] ? '<span class="symbol__error"></span> Only VIP members can view this content.' : $content);
	
}

/* Updates */
if($_SESSION['username'] === 'inartistic') {
$sql_recent = "
	SELECT recent.*, users.username
	FROM (
		(
			SELECT 'artist' AS type, artist_id AS item_id, user_id, date_occurred AS date_edited
			FROM edits_artists
			GROUP BY edits_artists.artist_id
			ORDER BY edits_artists.date_occurred DESC LIMIT 20
		)
		UNION
		(
			SELECT 'company' AS type, label_id AS item_id, user_id, date_occurred AS date_edited
			FROM edits_labels
			GROUP BY edits_labels.label_id
			ORDER BY edits_labels.date_occurred DESC LIMIT 7
		)
		UNION
		(
			SELECT 'release' AS type, release_id AS item_id, user_id, date_occurred AS date_edited
			FROM edits_releases
			GROUP BY edits_releases.release_id
			ORDER BY edits_releases.date_occurred DESC LIMIT 20
		)
	) AS recent
	LEFT JOIN users ON users.id=recent.user_id
	ORDER BY recent.date_edited DESC
";

$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute();
$rslt_recent = $stmt_recent->fetchAll();
	
	
//$num_updates = count($updates);
	
	print_r($updates);
}
else {
$sql_recent = "
	SELECT recent.*, users.username
	FROM (
		(
			SELECT 'artist' AS type, edits_artists.user_id, CONCAT_WS('/', '', 'artists', friendly, '') AS url, COALESCE(romaji, name) AS quick_name, edits_artists.date_occurred AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_artists
			LEFT JOIN artists ON artists.id=edits_artists.artist_id
			GROUP BY edits_artists.artist_id
			ORDER BY edits_artists.date_occurred DESC LIMIT 20
		)
		UNION
		(
			SELECT 'company' AS type, edits_labels.user_id, CONCAT_WS('/', '', 'labels', friendly, '') AS url, COALESCE(romaji, name) AS quick_name, edits_labels.date_occurred AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_labels
			LEFT JOIN labels ON labels.id=edits_labels.label_id
			GROUP BY edits_labels.label_id
			ORDER BY edits_labels.date_occurred DESC LIMIT 7
		)
		UNION
		(
			SELECT 'release' AS type, edits_releases.user_id, CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url, CONCAT_WS(' ', COALESCE(releases.romaji, releases.name, ''), COALESCE(press_romaji, press_name, ''), COALESCE(type_romaji, type_name, '')) AS quick_name, edits_releases.date_occurred AS date_edited, COALESCE(artists.romaji, artists.name) AS artist_quick_name, CONCAT_WS('/', '', 'artists', artists.friendly, '') AS artist_url
			FROM edits_releases
			LEFT JOIN releases ON releases.id=edits_releases.release_id
			LEFT JOIN artists ON artists.id=releases.artist_id
			GROUP BY edits_releases.release_id
			ORDER BY edits_releases.date_occurred DESC LIMIT 20
		)
	) AS recent
	LEFT JOIN users ON users.id=recent.user_id
	ORDER BY recent.date_edited DESC
";

$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute();
$updates = $stmt_recent->fetchAll();
$num_updates = count($updates);
}

/* Artist of day */
$sql_aod = "SELECT artists.id, artists.description FROM queued_aod LEFT JOIN artists ON artists.id=queued_aod.artist_id ORDER BY queued_aod.date_occurred DESC LIMIT 1";
$stmt_aod = $pdo->prepare($sql_aod);
$stmt_aod->execute();
$rslt_aod = $stmt_aod->fetch();
$rslt_aod = is_array($rslt_aod) ? $rslt_aod : [];
$addl_aod = $access_artist->access_artist(["id" => $rslt_aod["id"], "get" => "name"]);
$addl_aod = is_array($addl_aod) ? $addl_aod : [];
$artist_of_day = array_merge($rslt_aod, $addl_aod);

/* Flyer of day */
$image = $access_image->access_image([ 'flyer_of_day' => true, 'get' => 'all' ])[0];
if(is_array($image) && !empty($image) && file_exists("../images/image_files/".$image["id"].".".$image["extension"])) {
	$image["size"] = getimagesize("../images/image_files/".$image["id"].".".$image["extension"]);
	$image["is_wide"] = is_array($image["size"]) && !empty($image["size"]) && $image["size"][0] > $image["size"][1];
}

/* Artist rankings */
$sql_rankings = "
	SELECT
		SUM(artists_views.view_count) AS view_count,
		COALESCE(artists.romaji, artists.name) AS quick_name,
		artists.friendly,
		artists.name
	FROM artists_views
	LEFT JOIN artists ON artists.id=artists_views.artist_id
	WHERE artists_views.date_occurred > ? AND artists_views.date_occurred < ?
	GROUP BY artists_views.artist_id
	ORDER BY view_count DESC
	LIMIT 3
";
$stmt_rankings = $pdo->prepare($sql_rankings);
$stmt_rankings->execute([
	date("Y-m-d", strtotime("-2 weeks sunday", time())),
	date("Y-m-d", strtotime("-1 weeks sunday", time()))
]);
$rslt_rankings = $stmt_rankings->fetchAll();

/* Points ranks */
$access_points = new access_points($pdo);
$point_ranking = $access_points->access_points([
	'get' => 'ranking',
	'start_date' => date("Y-m-d", strtotime("-2 weeks sunday", time())),
	'end_date' => date("Y-m-d", strtotime("-1 weeks sunday", time())),
	'limit' => 3,
]);

/* VIP users */
$sql_vip_users = "SELECT username FROM users WHERE is_vip=? ORDER BY username";
$stmt_vip_users = $pdo->prepare($sql_vip_users);
$stmt_vip_users->execute([ "1" ]);
$rslt_vip_users = $stmt_vip_users->fetchAll();

/* Artist tags */
$sql_artist_tags = "SELECT COUNT(*) AS num_tagged, tags_artists.name, tags_artists.romaji, tags_artists.friendly FROM artists_tags LEFT JOIN tags_artists ON tags_artists.id=artists_tags.tag_id GROUP BY artists_tags.tag_id HAVING num_tagged > 0 ORDER BY tags_artists.friendly ASC";
$stmt_artist_tags = $pdo->prepare($sql_artist_tags);
$stmt_artist_tags->execute();
$rslt_artist_tags = $stmt_artist_tags->fetchAll();

/* Release tags */
$sql_release_tags = "SELECT COUNT(*) AS num_tagged, tags_releases.name, tags_releases.romaji, tags_releases.friendly FROM releases_tags LEFT JOIN tags_releases ON tags_releases.id=releases_tags.tag_id GROUP BY releases_tags.tag_id HAVING num_tagged > 0 ORDER BY tags_releases.friendly ASC";
$stmt_release_tags = $pdo->prepare($sql_release_tags);
$stmt_release_tags->execute();
$rslt_release_tags = $stmt_release_tags->fetchAll();

include('../main/page-index.php');