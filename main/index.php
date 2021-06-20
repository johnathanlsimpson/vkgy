<?php

$access_blog = new access_blog($pdo);
$access_artist = new access_artist($pdo);
$access_comment = new access_comment($pdo);
$markdown_parser = new parse_markdown($pdo);
$access_user = new access_user($pdo);
$access_image = $access_image ?: new access_image($pdo);

/* Get cover art for latest releases */
$sql_covers = 'SELECT images.id, images.friendly, images.extension FROM releases LEFT JOIN images ON images.id=releases.image_id WHERE releases.image_id IS NOT NULL ORDER BY releases.date_occurred DESC LIMIT 20';
$stmt_covers = $pdo->prepare($sql_covers);
$stmt_covers->execute();
$covers = $stmt_covers->fetchAll();
shuffle($covers);

/* Get news */
$news = $access_blog->access_blog([ 'get' => 'list', 'limit' => 7 ]);
$num_news = count($news);

$news[0]["content"] = $markdown_parser->parse_markdown($news[0]["content"]);
$news[0]["comment_count"] = $access_comment->access_comment([ "id" => $news[0]["id"], "type" => "blog", "get" => "count" ]);
$news[0]["comment_text"] = ($news[0]["comment_count"] ? 'read '.$news[0]["comment_count"].' comment'.($news[0]["comment_count"] !== "1" ? 's' : null) : 'comment on this entry');

for($i=0; $i<$num_news; $i++) {
	$news[$i]["date_occurred"] = substr($news[$i]["date_occurred"], 0, 10);
	$news[$i]["image"] = str_replace(".", ".thumbnail.", $news[$i]["image"]);
}

/* Get comments */
$comments = $access_comment->access_comment(['is_deleted' => 0, "get" => "list", "limit" => 20, 'threads' => false]);
$num_comments = count($comments);

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
// Have to grab update data in several passes to increase speed
// First, do simple grab of XXX edit records, ordered by id DESC
// Then, join edit table back to self, group by item_id, and pare down to XX records
// Then, join appropriate item based on item_id
// And finally, union it all together

$sql_recent = "
	SELECT *
	FROM (

		(
			SELECT
				aaa.user_id,
				aaa.date_edited,

				'' AS name,
				'' AS romaji,
				'' AS url,
				'1' AS num_items,

				artists.name AS artist_name,
				artists.romaji AS artist_romaji,
				artists.image_id,
				CONCAT_WS('/', '', 'artists', artists.friendly, '') AS artist_url,

				'artist' AS type
			FROM
				(
					SELECT artist_id, user_id, date_occurred AS date_edited
					FROM
						(
							SELECT id
							FROM edits_artists
							ORDER BY id DESC
							LIMIT 40
						) aa
					LEFT JOIN edits_artists ON edits_artists.id=aa.id
					GROUP BY edits_artists.artist_id
					ORDER BY edits_artists.date_occurred DESC
				) aaa
			LEFT JOIN artists ON artists.id=aaa.artist_id AND artists.is_vkei>-1
			WHERE artists.id IS NOT NULL
			LIMIT 6
		)

		UNION

		(
			SELECT
				ccc.user_id,
				ccc.date_edited,

				CONCAT_WS(' ', COALESCE(releases.name, ''), COALESCE(releases.press_name, ''), COALESCE(releases.type_name, '')) AS name,
				CONCAT_WS(' ', COALESCE(releases.romaji, releases.name, ''), COALESCE(releases.press_romaji, releases.press_name, ''), COALESCE(releases.type_romaji, releases.type_name, '')) AS romaji,
				CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url,
				COUNT(artists.id) AS num_items,

				artists.name AS artist_name,
				artists.romaji AS artist_romaji,
				'' AS image_id,
				CONCAT_WS('/', '', 'artists', artists.friendly, '') AS artist_url,

				'release' AS type
			FROM
				(
					SELECT release_id, user_id, date_occurred AS date_edited
					FROM
						(
							SELECT id
							FROM edits_releases
							ORDER BY id DESC
							LIMIT 30
						) cc
					LEFT JOIN edits_releases ON edits_releases.id=cc.id
					GROUP BY edits_releases.release_id
				) ccc
			LEFT JOIN releases ON releases.id=ccc.release_id
			LEFT JOIN artists ON artists.id=releases.artist_id
			GROUP BY artists.id
		)

	) AS recent
	ORDER BY type ASC, date_edited DESC
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
$sql_rank = '
	SELECT
		views_artists_weekly.*,
		(COALESCE(views_artists_weekly.past_views, 0) - COALESCE(views_artists_weekly.past_past_views, 0)) AS num_difference,
		artists.name,
		artists.romaji,
		artists.friendly,
		COALESCE(artists.romaji, artists.name) AS quick_name
	FROM
		views_artists_weekly
	LEFT JOIN
		artists ON artists.id=views_artists_weekly.artist_id
	ORDER BY
		num_difference DESC,
		past_views DESC
	LIMIT 3
';
$stmt_rank = $pdo->prepare($sql_rank);
$stmt_rank->execute();
$rslt_rank = $stmt_rank->fetchAll();

/* Points ranks */
$sql_points = '
	SELECT weekly_points.user_id, users.username, SUM(weekly_points.point_value) AS points_value
	FROM (
		SELECT user_id, point_value
		FROM users_points
		WHERE date_occurred>=? AND date_occurred<=?
	) weekly_points
	LEFT JOIN users ON users.id=weekly_points.user_id
	GROUP BY weekly_points.user_id
	ORDER BY points_value DESC
	LIMIT 3';
$stmt_points = $pdo->prepare($sql_points);
$stmt_points->execute([ $rank_start, $rank_end ]);
$point_ranking = $stmt_points->fetchAll();

foreach($point_ranking as $point_key => $point) {
	$access_points = new access_points($pdo);
	$user_level = $access_points->access_points([ 'user_id' => $point['user_id'], 'get' => 'level' ]);
	$point_ranking[$point_key]['level'] = $user_level[0]['level'];
}

/*$access_points = new access_points($pdo);
$point_ranking = $access_points->access_points([
	'get' => 'ranking',
	'start_date' => date("Y-m-d", strtotime("-2 weeks sunday", time())),
	'end_date' => date("Y-m-d", strtotime("-1 weeks sunday", time())),
	'limit' => 3
]);*/

/* VIP users */
$sql_vip_users = "SELECT username FROM users WHERE is_vip=? ORDER BY username";
$stmt_vip_users = $pdo->prepare($sql_vip_users);
$stmt_vip_users->execute([ "1" ]);
$rslt_vip_users = $stmt_vip_users->fetchAll();

/* Artist tags */
include_once('../php/class-tag.php');
$access_tag = new tag($pdo);
$artist_tags = $access_tag->access_tag([ 'item_type' => 'artist', 'get' => 'basics', 'flat' => true ]);

/* Release tags */
$release_tags = $access_tag->access_tag([ 'item_type' => 'release', 'get' => 'basics', 'flat' => true ]);

/* News */

// Get URLs
foreach($news as $news_key => $news_item) {
	
	// Set URL
	$news[$news_key]['url'] = '/blog/'.$news_item['friendly'].'/';
	
	// Set thumbnail
	if($news[$news_key]['image']) {
		$news[$news_key]['image']['url'] = str_replace('.thumbnail.', '.small.', $news_item['image']['url']);
	}
	else {
		$news[$news_key]['image'] = ['url' => null];
	}
	
}

/* Logic for featured cards */

// Get latest news
$latest_news = $news[0];

// Get latest interview
$latest_interviews = $access_blog->access_blog([ 'tag' => 'interview', 'get' => 'basics', 'limit' => 3 ]);

// Mark all interviews as being interview
foreach($latest_interviews as $interview_key => $latest_interview) {
	$latest_interviews[$interview_key]['is_interview'] = true;
}

// Save latest interview
$latest_interview = $latest_interviews[0];

// Get a few other news articles, skipping 0th, which will be shuffled later
for($i=1; $i<=4; $i++) {
	$latest_items[] = $news[$i];
}

// Shuffle items
shuffle($latest_items);

// Get some dates
$yesterday = date('Y-m-d', strtotime('yesterday'));
$last_month = date('Y-m-d', strtotime('1 month ago'));

// If interview in last day, that should be first no matter what
$latest_item = $latest_interview['date_occurred'] > $yesterday ? $latest_interview : $latest_news;

// Push latest item to front
array_unshift( $latest_items, $latest_item );

// Remove all but 3 items
$latest_items = array_slice( $latest_items, 0, ( $latest_interview['date_occurred'] > $yesterday ? 4 : 3 ) );

// If interview older than yesterday, push an interview to the end
if($latest_interview['date_occurred'] < $yesterday) {
	
	// If latest interview older than one month, push random interview
	if($latest_interview['date_occurred'] < $last_month) {
		shuffle($latest_interviews);
	}
	
	// Push
	$latest_items[] = $latest_interviews[0];
	
}

// Loop through items and get URL and image URLs
foreach($latest_items as $item_key => $latest_item) {
	
	// URL
	$latest_items[$item_key]['url'] = '/blog/'.$latest_item['friendly'].'/';
	
	// Decide pill
	if($latest_item['is_interview']) {
		$pill = 'interview';
	}
	else {
		if( $item_key == 0 && $latest_item['date_occurred'] > date('Y-m-d', strtotime('1 day ago')) ) {
			$pill = 'breaking';
		}
		else {
			$pill = 'news';
		}
	}
	$latest_items[$item_key]['pill'] = $pill;
	
	// Image
	$image = $latest_item['image'];
	
	// Some URLs are given as thumbnail
	if($image && $image['url']) {
		$url = str_replace('.thumbnail.', '.', $image['url']);
		$url = str_replace('.small.', '.', $url);
		$medium_url = str_replace('.', '.medium.', $url);
		$thumbnail_url = str_replace('.', '.thumbnail.', $url);
	}
	
	// Push
	$latest_items[$item_key]['image'] = array_merge( $latest_items[$item_key]['image'], [
		'url' => $url ?: null,
		'medium_url' => $medium_url ?: null,
		'thumbnail_url' => $thumbnail_url ?: null
	] );
	
}

include('../main/page-index.php');