<?php

style([
	'/account/style-partial-activity.css'
]);

// For specified tables, cycle through and start setting up query to get user's activity
$activity_tables = [
	'artists_tags',
	'blog',
	'comments',
	'comments_likes',
	'edits_artists',
	'edits_labels',
	'edits_musicians',
	'edits_releases',
	'images',
	'lives',
	'lives_livehouses',
	'releases',
	'releases_collections',
	'releases_ratings',
	'releases_tags',
	'releases_wants',
	'videos'
];

$group_activity_by = [
	'artists_tags' => 'artist_id',
	'edits_artists' => 'artist_id',
	'edits_labels' => 'label_id',
	'edits_musicians' => 'musician_id',
	'edits_releases' => 'release_id',
	'releases_tags' => 'release_id',
];

foreach($activity_tables as $activity_table) {
	$sql_activity[] = '
	(
		SELECT
			id, 
			'.(in_array($activity_table, [ 'images', 'lives_livehouses', 'releases_ratings' ]) ? 'date_added AS date_occurred' : 'date_occurred').', 
			"'.$activity_table.'" AS type 
		FROM 
			'.$activity_table.' 
		WHERE
			user_id=? 
		'.($group_activity_by[$activity_table] ? 'GROUP BY '.$activity_table.'.'.$group_activity_by[$activity_table] : null).'
		ORDER BY 
			id DESC 
		LIMIT
		'.$activity_limit.'
	)';
	$values_activity[] = $user['id'];
}

// Get activity from other users regarding current user
$sql_activity[] = '(SELECT comments_likes.id, comments_likes.date_occurred, "external_comments_likes" AS type FROM comments LEFT JOIN comments_likes ON comments_likes.comment_id=comments.id WHERE comments.user_id=? ORDER BY comments_likes.date_occurred DESC LIMIT '.$activity_limit.')';
$values_activity[] = $user['id'];

// First activity query
$sql_activity = 'SELECT * FROM ( '.implode(' UNION ', $sql_activity).' ) activity ORDER BY date_occurred DESC LIMIT '.$activity_offset.', '.$activity_limit;
$stmt_activity = $pdo->prepare($sql_activity);
$stmt_activity->execute( $values_activity );
$rslt_activity = $stmt_activity->fetchAll();

// Generate links for each activity type
foreach($rslt_activity as $activity) {
	switch($activity['type']) {

		case('artists_tags'):
			$activity_name = 'artists.name';
			$activity_romaji = 'artists.romaji';
			$activity_url = 'CONCAT_WS("/", "", "artists", artists.friendly, "")';
			$activity_join = 'artists ON artists.id=artists_tags.artist_id';
			break;
		case('blog'):
			$singular = 'blog';
			$activity_name = 'blog.title';
			$activity_url = 'CONCAT_WS("/", "", "blog", blog.friendly, "")';
			$activity_join = null;
			break;
		case('comments'):
			$activity_name = 'comments.content';
			$activity_url = 'comments.id';
			$activity_join = null;
			break;
		case('comments_likes'):
			$activity_name = 'users.username';
			$activity_url = 'comments_likes.id';
			$activity_join = 'comments ON comments.id=comments_likes.comment_id LEFT JOIN users ON users.id=comments.user_id';
			break;
		case('external_comments_likes'):
			$activity_name = 'users.username';
			$activity_url = 'comments_likes.id';
			$activity_from = 'comments_likes';
			$activity_join = 'comments ON comments.id=comments_likes.comment_id LEFT JOIN users ON users.id=comments_likes.user_id';
			break;
		case('edits_artists'):
			$activity_name = 'artists.name';
			$activity_romaji = 'artists.romaji';
			$activity_url = 'CONCAT_WS("/", "", "artists", artists.friendly, "")';
			$activity_join = 'artists ON artists.id=edits_artists.artist_id';
			break;
		case('edits_labels'):
			$activity_name = 'labels.name';
			$activity_romaji = 'labels.romaji';
			$activity_url = 'CONCAT_WS("/", "", "labels", labels.friendly, "")';
			$activity_join = 'labels ON labels.id=edits_labels.label_id';
			break;
		case('edits_musicians'):
			$activity_name = 'musicians.name';
			$activity_romaji = 'musicians.romaji';
			$activity_url = 'CONCAT_WS("/", "", "musicians", musicians.id, musicians.friendly, "")';
			$activity_join = 'musicians ON musicians.id=edits_musicians.musician_id';
			break;
		case('edits_releases'):
			$activity_name = 'CONCAT_WS(" ", releases.name, COALESCE(releases.press_name, ""), COALESCE(releases.type_name, ""))';
			$activity_romaji = 'CONCAT_WS(" ", COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name, ""), COALESCE(releases.type_romaji, releases.type_name, ""))';
			$activity_url = 'CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "")';
			$activity_join = 'releases ON releases.id=edits_releases.release_id LEFT JOIN artists ON artists.id=releases.artist_id';
			$activity_group = 'edits_releases.release_id';
			break;
		case('images'):
			$activity_url = 'CONCAT("/images/", images.id, ".", images.extension)';
			$activity_join = null;
			break;
		case('lives'):
			$activity_url = 'CONCAT_WS("/", "", "lives", "&id=", lives.id)';
			$activity_join = null;
			break;
		case('lives_livehouses'):
			$activity_name = 'lives_livehouses.name';
			$activity_romaji = 'lives_livehouses.romaji';
			$activity_url = 'CONCAT_WS("/", "", "lives", "&livehouse_id=", lives_livehouses.id)';
			$activity_join = null;
			break;
		case('releases'):
			$activity_name = 'CONCAT_WS(" ", releases.name, COALESCE(releases.press_name, ""), COALESCE(releases.type_name, ""))';
			$activity_romaji = 'CONCAT_WS(" ", COALESCE(releases.romaji, releases.name), COALESCE(releases.press_romaji, releases.press_name, ""), COALESCE(releases.type_romaji, releases.type_name, ""))';
			$activity_url = 'CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "")';
			$activity_join = 'artists ON artists.id=releases.artist_id';
			break;
		case('releases_collections'):
			$activity_name = 'releases.name';
			$activity_romaji = 'releases.romaji';
			$activity_url = 'CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "")';
			$activity_join = 'releases ON releases.id=releases_collections.release_id LEFT JOIN artists ON artists.id=releases.artist_id';
			break;
		case('releases_ratings'):
			$activity_name = 'releases.name';
			$activity_romaji = 'releases.romaji';
			$activity_url = 'CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "")';
			$activity_join = 'releases ON releases.id=releases_ratings.release_id LEFT JOIN artists ON artists.id=releases.artist_id';
			break;
		case('releases_tags'):
			$activity_name = 'releases.name';
			$activity_romaji = 'releases.romaji';
			$activity_url = 'CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "")';
			$activity_join = 'releases ON releases.id=releases_tags.release_id LEFT JOIN artists ON artists.id=releases.artist_id';
			break;
		case('releases_wants'):
			$activity_name = 'releases.name';
			$activity_romaji = 'releases.romaji';
			$activity_url = 'CONCAT_WS("/", "", "releases", artists.friendly, releases.id, releases.friendly, "")';
			$activity_join = 'releases ON releases.id=releases_wants.release_id LEFT JOIN artists ON artists.id=releases.artist_id';
			break;
		case('videos'):
			$activity_url = 'CONCAT_WS("/", "", "artists", artists.friendly, "videos", "")';
			$activity_join = 'artists ON artists.id=videos.artist_id';
			break;
		default:
			$activity_url = 'id';
			$activity_join = null;
	}

	$sql_activity_urls[] = '
		SELECT 
			"'.$activity['id'].'" AS id, 
			'.( $activity_url ?: 'NULL' ).' AS url, 
			'.( $activity_name ?: 'NULL' ).' AS name, 
			'.( $activity_romaji ?: 'NULL' ).' AS romaji 
		FROM 
			'.($activity_from ?: $activity['type']).' '.
			( $activity_join ? 'LEFT JOIN '.$activity_join : null ).' 
		WHERE
			'.($activity_from ?: $activity['type']).'.id=?';
	$values_activity_urls[] = $activity['id'];

	unset($activity_name, $activity_romaji, $activity_url, $activity_from, $activity_join, $singular);
}

$sql_activity_urls = 'SELECT * FROM ( ('.implode(') UNION (', $sql_activity_urls).') ) activity_urls';
$stmt_activity_urls = $pdo->prepare($sql_activity_urls);
$stmt_activity_urls->execute( $values_activity_urls );
$rslt_activity_urls = $stmt_activity_urls->fetchAll();

foreach($rslt_activity_urls as $activity_key => $activity_url) {
	$rslt_activity[$activity_key] = $rslt_activity[$activity_key] + $activity_url;
}

foreach($rslt_activity as $activity) {
	switch($activity['type']) {
		case('artist_tags'):
			$symbol = 'tag';
			$supertitle = $user['username'].'tagged <a class="a--inherit symbol__artist" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('blog'):
			$symbol = 'news';
			$supertitle = $user['username'].' added a blog post.';
			$content = '<a href="'.$activity['url'].'">'.$activity['name'].'</a>';
			break;
		case('comments'):
			$symbol = 'edit';
			$supertitle = $user['username'].' commented.';
			$content = $activity['name'];
			$image = '/usericons/avatar-'.$user['username'].'.png';
			break;
		case('comments_likes'):
			$symbol = 'like';
			$supertitle = $user['username'].' liked a comment by '.($activity['name'] ? '<a class="a--inherit symbol__user" href="/users/'.$activity['name'].'/">'.$activity['name'].'</a>' : 'an anonymous user').'.';
			break;
		case('comments_external_likes'):
			$symbol = 'like';
			$supertitle = '<a class="symbol__user a--inherit" href="/users/'.$activity['name'].'">'.$activity['name'].'</a> liked a comment by '.$user['username'].'.';
			break;
		case('edits_artists'):
			$symbol = 'artist';
			$supertitle = $user['username'].' edited artist <a class="a--inherit symbol__artist" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('edits_labels'):
			$symbol = 'company';
			$supertitle = $user['username'].' edited label <a class="a--inherit symbol__company" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('edits_musicians'):
			$symbol = 'musician';
			$supertitle = $user['username'].' edited musician <a class="a--inherit symbol__musician" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('edits_releases'):
			$symbol = 'release';
			$supertitle = $user['username'].' edited release <a class="a--inherit symbol__release" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('images'):
			$symbol = 'plus';
			$supertitle = $user['username'].' added <a class="a--inherit" href="'.$activity['url'].'">an image</a>.';
			$image = str_replace('.', '.thumbnail.', $activity['url']);
			break;
		case('lives'):
			$symbol = 'ticket';
			$supertitle = $user['username'].' added <a class="a--inherit" href="'.$activity['url'].'">a live.</a>';
			break;
		case('lives_livehouses'):
			$symbol = 'company';
			$supertitle = $user['username'].' added livehouse <a class="symbol__company a--inherit" href="'.$activity['url'].'">'.($activity['romaji'] ?: $activity['name']).'</a>.';
			break;
		case('releases'):
			$symbol = 'release';
			$supertitle = $user['username'].' added <a class="a--inherit symbol__release" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('releases_collections'):
			$symbol = 'register';
			$supertitle = $user['username'].' collected <a class="a--inherit symbol__release" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('releases_ratings'):
			$symbol = 'star--half';
			$supertitle = $user['username'].' rated <a class="a--inherit symbol__release" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('releases_tags'):
			$symbol = 'tag';
			$supertitle = $user['username'].' tagged <a class="a--inherit symbol__release" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('releases_wants'):
			$symbol = 'search';
			$supertitle = $user['username'].' is looking for '.'<a class="a--inherit symbol__release" href="'.$activity['url'].'">'.lang($activity['romaji'] ?: $activity['name'], $activity['name'], 'hidden').'</a>.';
			break;
		case('videos'):
			$symbol = 'plus';
			$supertitle = $user['username'].' added <a class="a--inherit" href="'.$activity['url'].'">a video</a>.';
			break;
		default: '';
	}
	
	$date_occurred = lang(
		$activity['date_occurred'] > date('Y-m-d', strtotime('7 days ago')) ? date('l', strtotime($activity['date_occurred'])) : date('F j', strtotime($activity['date_occurred'])).($activity['date_occurred'] > date('Y') ? null : ', '.substr($activity['date_occurred'], 0, 4)),
		($activity['date_occurred'] > date('Y') ? null : substr($activity['date_occurred'], 0, 4).'年').date('n月j日', strtotime($activity['date_occurred'])),
		'hidden'
	);
	?>
		<li class="flex activity__item" data-type="<?= $activity['type']; ?>">
			<span class="any--weaken-color activity__symbol <?= $image ? 'activity--has-image' : null; ?> <?= 'symbol__'.$symbol; ?>">
				<?= $image ? '<img class="activity__image" src="'.$image.'" />' : null; ?>
			</span>
			
			<span>
				<span class="any--weaken-size activity__subtitle">
					<?= $supertitle; ?>
					<span class="any--weaken-color activity__date"><?= $date_occurred; ?></span>
				</span>
				<div class="activity__content"><?= $content; ?></div>
			</span>
			
			<?php
				unset($supertitle, $content, $image);
			?>
		</li>
	<?php
}