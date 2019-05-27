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

subnav([
	"Add news" => "/blog/add/",
	"Database" => "/database/",
]);

$access_image = $access_image ?: new access_image($pdo);
//if($_SESSION["username"] === "inartistic") { include_once("../main/function-choose_aod.php"); }

/* Check VIP */
if($_SESSION["loggedIn"] && is_numeric($_SESSION["userID"])) {
	$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
	$stmt_check = $pdo->prepare($sql_check);
	$stmt_check->execute([ $_SESSION["userID"] ]);
	$is_vip = $stmt_check->fetchColumn();
}

if($is_vip) {
	subnav([
		"VIP" => "/vip/"
	]);
}

/* Get VIP news */
if($is_vip) {
	$sql_vip = "SELECT vip.title, vip.friendly, vip.date_occurred, vip_views.id AS is_viewed FROM vip LEFT JOIN vip_views ON vip_views.post_id=vip.id AND vip_views.user_id=? ORDER BY vip.date_occurred DESC LIMIT 1";
	$stmt_vip = $pdo->prepare($sql_vip);
	$stmt_vip->execute([ $_SESSION["userID"] ]);
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

foreach($comments as $key => $comment) {
	if($comment["item_type"] === "blog") {
		$sql_comment = "SELECT CONCAT_WS('/', '', 'blog', friendly, '') AS url FROM blog WHERE id=?";
	}
	elseif($comment["item_type"] === "release") {
		$sql_comment = "SELECT CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url FROM releases LEFT JOIN artists ON artists.id=releases.artist_id WHERE releases.id=?";
	}
	elseif($comment["item_type"] === "artist") {
		$sql_comment = "SELECT CONCAT_WS('/', '', 'artists', friendly, '') AS url FROM artists WHERE id=?";
	}
	elseif($comment["item_type"] === "vip") {
		$sql_comment = "SELECT CONCAT_WS('/', '', 'vip', friendly, '') AS url FROM vip WHERE id=?";
	}

	if($sql_comment) {
		$stmt_comment = $pdo->prepare($sql_comment);
		$stmt_comment->execute([$comment["item_id"]]);
		$comments[$key]["url"] = $stmt_comment->fetchColumn();
	}

	$comments[$key]["date_occurred"] = substr($comments[$key]["date_occurred"], 0, 10);

	$content = trim($comment["content"]);
	$content = explode("\n", $content)[0];
	$content = $markdown_parser->parse_markdown($content);
	$content = str_replace(["<p>", "</p>"], "", $content);
	$content = trim($content);
	$comments[$key]["content"] = ($comment["item_type"] === "vip" && !$is_vip ? '<span class="symbol__error"></span> Only VIP members can view this content.' : $content);

	unset($sql_comment);
}

/* Avatars */
include_once("../avatar/class-avatar.php");
include_once("../avatar/avatar-options.php");
include_once("../avatar/avatar-definitions.php");

for($i=0; $i<$num_comments; $i++) {
	$sql_avatar = "SELECT content FROM users_avatars WHERE user_id=? LIMIT 1";
	$stmt_avatar = $pdo->prepare($sql_avatar);
	$stmt_avatar->execute([ $comments[$i]["user"]["id"] ]);
	$rslt_avatar = $stmt_avatar->fetchColumn();

	$comments[$i]["user"]["avatar_class"] = (!$rslt_avatar ? 'comment__no-avatar' : null);
	$rslt_avatar = $rslt_avatar ?: '{"head__base":"default","head__base-color":"i"}';

	$avatar = new avatar($avatar_layers, $rslt_avatar, ["is_vip" => true]);
	$comments[$i]["user"]["avatar"] = $avatar->get_avatar_paths();

	unset($avatar);
}

/* Updates */
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
		artists.friendly
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