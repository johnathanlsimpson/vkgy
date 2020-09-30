<?php

$access_blog = new access_blog($pdo);
$access_artist = new access_artist($pdo);
$access_comment = new access_comment($pdo);
$markdown_parser = new parse_markdown($pdo);

$access_image = $access_image ?: new access_image($pdo);

/* Get VIP news */
if($_SESSION['is_vip']) {
	$sql_vip = "SELECT vip.title, vip.friendly, vip.date_occurred, vip_views.id AS is_viewed FROM vip LEFT JOIN vip_views ON vip_views.post_id=vip.id AND vip_views.user_id=? ORDER BY vip.date_occurred DESC LIMIT 1";
	$stmt_vip = $pdo->prepare($sql_vip);
	$stmt_vip->execute([ $_SESSION['user_id'] ]);
	$rslt_vip = $stmt_vip->fetch();
}

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
				COALESCE(artists.romaji, artists.name) AS quick_name,
				CONCAT_WS('/', '', 'artists', artists.friendly, '') AS url,
				'artist' AS type,
				'' AS artist_quick_name,
				'' AS artist_url
			FROM
				(
					SELECT artist_id, user_id, date_occurred AS date_edited
					FROM
						(
							SELECT id
							FROM edits_artists
							ORDER BY id DESC
							LIMIT 30
						) aa
					LEFT JOIN edits_artists ON edits_artists.id=aa.id
					GROUP BY edits_artists.artist_id
					LIMIT 20
				) aaa
			LEFT JOIN artists ON artists.id=aaa.artist_id
		)
		
		UNION
		
		(
			SELECT
				ccc.user_id,
				ccc.date_edited,
				CONCAT_WS(' ', COALESCE(releases.romaji, releases.name, ''), COALESCE(releases.press_romaji, releases.press_name, ''), COALESCE(releases.type_romaji, releases.type_name, '')) AS quick_name,
				CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url,
				'release' AS type,
				COALESCE(artists.romaji, artists.name) AS artist_quick_name,
				CONCAT_WS('/', '', 'artists', artists.friendly, '') AS artist_url
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
					LIMIT 20
				) ccc
			LEFT JOIN releases ON releases.id=ccc.release_id
			LEFT JOIN artists ON artists.id=releases.artist_id
		)
		
	) AS recent
	ORDER BY date_edited DESC
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
		views_weekly_artists.*,
		(COALESCE(views_weekly_artists.past_views, 0) - COALESCE(views_weekly_artists.past_past_views, 0)) AS num_difference,
		artists.name,
		artists.romaji,
		artists.friendly,
		COALESCE(artists.romaji, artists.name) AS quick_name
	FROM
		views_weekly_artists
	LEFT JOIN
		artists ON artists.id=views_weekly_artists.artist_id
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
$sql_artist_tags = "SELECT COUNT(*) AS num_tagged, tags_artists.name, tags_artists.romaji, tags_artists.friendly FROM artists_tags LEFT JOIN tags_artists ON tags_artists.id=artists_tags.tag_id GROUP BY artists_tags.tag_id HAVING num_tagged > 0 ORDER BY tags_artists.friendly ASC";
$stmt_artist_tags = $pdo->prepare($sql_artist_tags);
$stmt_artist_tags->execute();
$rslt_artist_tags = $stmt_artist_tags->fetchAll();

/* Release tags */
$sql_release_tags = "SELECT COUNT(*) AS num_tagged, tags_releases.name, tags_releases.romaji, tags_releases.friendly FROM releases_tags LEFT JOIN tags_releases ON tags_releases.id=releases_tags.tag_id GROUP BY releases_tags.tag_id HAVING num_tagged > 0 ORDER BY tags_releases.friendly ASC";
$stmt_release_tags = $pdo->prepare($sql_release_tags);
$stmt_release_tags->execute();
$rslt_release_tags = $stmt_release_tags->fetchAll();

// Get VIP patrons
$access_user = new access_user($pdo);
$patrons = $access_user->access_user([ 'is_vip' => true ]);

// Get non-VIP patrons
foreach([ 'redaudrey' ] as $non_vip_patron) {
	$patrons[] = $access_user->access_user([ 'username' => $non_vip_patron ]);
}

// Sort patrons
usort($patrons, function($a, $b) { return strtolower($a['username']) <=> strtolower($b['username']); });

// Make sure icons exist
$num_patrons = count($patrons);
for($i=0; $i<$num_patrons; $i++) {
	if(!file_exists('..'.$patrons[$i]['avatar_url'])) {
		$patrons[$i]['avatar_url'] = '/usericons/avatar-anonymous.png';
	}
}

// Get dummy patrons to fill gaps in layout
$patron_columns = 3;
$patron_modulo = count($patrons) % $patron_columns;
while($patron_modulo) {
	$patrons[] = [ 'avatar_url' => '/usericons/avatar-anonymous.png' ];
	$patron_modulo = count($patrons) % $patron_columns;
}

/* News */

// Get URLs
foreach($news as $news_key => $news_item) {
	
	// Set URL
	$news[$news_key]['url'] = '/blog/'.$news_item['friendly'].'/';
	
	// Set thumbnail
	if($news[$news_key]['image']) {
		$news[$news_key]['image']['url'] = str_replace('.thumbnail.', '.small.', $news_item['image']['url']);
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
$latest_items = array_slice( $latest_items, 0, 3 );

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
		
		// Push
		$latest_items[$item_key]['image'] = array_merge( $latest_items[$item_key]['image'], [
			'url' => $url,
			'medium_url' => $medium_url,
			'thumbnail_url' => $thumbnail_url
		] );
	}
	
}

include('../main/page-index.php');