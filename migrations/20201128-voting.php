<?php

include_once('../php/include.php');

/*$sql[] = '
CREATE TABLE IF NOT EXISTS votes_development (
  id int(11) NOT NULL,
  item_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  score tinyint(4) NOT NULL DEFAULT 0,
  date_occurred datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;';

$sql[] = '
ALTER TABLE votes_development
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY item_id_2 (item_id,user_id),
  ADD KEY item_id (item_id),
  ADD KEY user_id (user_id);';

$sql[] = '
ALTER TABLE votes_development
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;';

$sql[] = '
CREATE TABLE IF NOT EXISTS votes_artists_tags (
  id int(11) NOT NULL,
  item_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  score tinyint(4) NOT NULL DEFAULT 0,
  date_occurred datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;';

$sql[] = '
ALTER TABLE votes_artists_tags
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY item_id_2 (item_id,user_id),
  ADD KEY item_id (item_id),
  ADD KEY user_id (user_id);';

$sql[] = '
ALTER TABLE votes_artists_tags
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;';*/

/*$sql[] = '
CREATE TABLE IF NOT EXISTS votes_musicians_tags (
  id int(11) NOT NULL,
  item_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  score tinyint(4) NOT NULL DEFAULT 0,
  date_occurred datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;';

$sql[] = '
ALTER TABLE votes_musicians_tags
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY item_id_2 (item_id,user_id),
  ADD KEY item_id (item_id),
  ADD KEY user_id (user_id);';

$sql[] = '
ALTER TABLE votes_musicians_tags
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;';*/

/*$sql[] = '
CREATE TABLE IF NOT EXISTS votes_releases_tags (
  id int(11) NOT NULL,
  item_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  score tinyint(4) NOT NULL DEFAULT 0,
  date_occurred datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;';

$sql[] = '
ALTER TABLE votes_releases_tags
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY item_id_2 (item_id,user_id),
  ADD KEY item_id (item_id),
  ADD KEY user_id (user_id);';

$sql[] = '
ALTER TABLE votes_releases_tags
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;';*/


////////////////////////////////////////////////////


/*// Move artists_tags votes to votes_artists_tags

$sql_artists_tags = 'SELECT * FROM artists_tags ORDER BY artist_id, tag_id';
$stmt_artists_tags = $pdo->prepare($sql_artists_tags);
$stmt_artists_tags->execute();
$rslt_artists_tags = $stmt_artists_tags->fetchAll();

$tmp_artists_tags = [];

foreach($rslt_artists_tags as $row) {
	
	$unique = $row['artist_id'].'-'.$row['tag_id'];
	$another_unique = $row['artist_id'].'-'.$row['tag_id'].'-'.$row['user_id'];
	
	$tmp_artists_tags[$unique]['id'] = $row['id'];
	$tmp_artists_tags[$unique]['data'][] = [
		'user_id' => $row['user_id'],
		'date_occurred' => $row['date_occurred'],
		'score' => $row['user_agrees'],
	];
	
}

$sql_votes_artists_tags = 'INSERT INTO votes_artists_tags (item_id, user_id, score, date_occurred) VALUES ';
foreach($tmp_artists_tags as $group) {
	foreach($group['data'] as $row) {
		$sql_votes_artists_tags .= '('.$group['id'].', '.$row['user_id'].', '.$row['score'].', "'.$row['date_occurred'].'"), ';
	}
}

$stmt_artists_tags = $pdo->prepare( substr($sql_votes_artists_tags, 0, -2) );
if($stmt_artists_tags->execute()) {
	echo 'inserted votes_artists_tags';
}
else {
	echo 'did not add votes';
}*/


////////////////////////////////////////////////////


/*// Move musicians_tags votes to votes_musicians_tags

$sql_musicians_tags = 'SELECT * FROM musicians_tags ORDER BY musician_id, tag_id';
$stmt_musicians_tags = $pdo->prepare($sql_musicians_tags);
$stmt_musicians_tags->execute();
$rslt_musicians_tags = $stmt_musicians_tags->fetchAll();

$tmp_musicians_tags = [];

foreach($rslt_musicians_tags as $row) {
	
	$unique = $row['musician_id'].'-'.$row['tag_id'];
	$another_unique = $row['musician_id'].'-'.$row['tag_id'].'-'.$row['user_id'];
	
	$tmp_musicians_tags[$unique]['id'] = $row['id'];
	$tmp_musicians_tags[$unique]['data'][] = [
		'user_id' => $row['user_id'],
		'date_occurred' => $row['date_occurred'],
		'score' => $row['user_agrees'],
	];
	
}

$sql_votes_musicians_tags = 'INSERT INTO votes_musicians_tags (item_id, user_id, score, date_occurred) VALUES ';
foreach($tmp_musicians_tags as $group) {
	foreach($group['data'] as $row) {
		$sql_votes_musicians_tags .= '('.$group['id'].', '.$row['user_id'].', '.$row['score'].', "'.$row['date_occurred'].'"), ';
	}
}

$stmt_musicians_tags = $pdo->prepare( substr($sql_votes_musicians_tags, 0, -2) );
if($stmt_musicians_tags->execute()) {
	echo 'inserted votes_musicians_tags';
}
else {
	echo 'did not add votes';
}*/


////////////////////////////////////////////////////


/*// Move releases_tags votes to votes_releases_tags

$sql_releases_tags = 'SELECT * FROM releases_tags ORDER BY release_id, tag_id';
$stmt_releases_tags = $pdo->prepare($sql_releases_tags);
$stmt_releases_tags->execute();
$rslt_releases_tags = $stmt_releases_tags->fetchAll();

$tmp_releases_tags = [];

foreach($rslt_releases_tags as $row) {
	
	$unique = $row['release_id'].'-'.$row['tag_id'];
	$another_unique = $row['release_id'].'-'.$row['tag_id'].'-'.$row['user_id'];
	
	$tmp_releases_tags[$unique]['id'] = $row['id'];
	$tmp_releases_tags[$unique]['data'][] = [
		'user_id' => $row['user_id'],
		'date_occurred' => $row['date_occurred'],
		'score' => $row['user_agrees'],
	];
	
}

$sql_votes_releases_tags = 'INSERT INTO votes_releases_tags (item_id, user_id, score, date_occurred) VALUES ';
foreach($tmp_releases_tags as $group) {
	foreach($group['data'] as $row) {
		$sql_votes_releases_tags .= '('.$group['id'].', '.$row['user_id'].', '.$row['score'].', "'.$row['date_occurred'].'"), ';
	}
}

$stmt_releases_tags = $pdo->prepare( substr($sql_votes_releases_tags, 0, -2) );
if($stmt_releases_tags->execute()) {
	echo 'inserted votes_releases_tags';
}
else {
	echo 'did not add votes';
}*/


////////////////////////////////////////////////////


/*// Delete unnecessary artists_tags

$sql_extant = 'SELECT item_id FROM votes_artists_tags GROUP BY item_id';
$stmt_extant = $pdo->prepare($sql_extant);
$stmt_extant->execute();
$rslt_extant = $stmt_extant->fetchAll(PDO::FETCH_COLUMN, 0);

$sql_del = 'DELETE FROM artists_tags WHERE id NOT IN ('.implode(',', $rslt_extant).')';
$stmt_del = $pdo->prepare($sql_del);
$stmt_del->execute();*/


////////////////////////////////////////////////////


/*// Delete unnecessary releases_tags

$sql_extant = 'SELECT item_id FROM votes_releases_tags GROUP BY item_id';
$stmt_extant = $pdo->prepare($sql_extant);
$stmt_extant->execute();
$rslt_extant = $stmt_extant->fetchAll(PDO::FETCH_COLUMN, 0);

$sql_del = 'DELETE FROM releases_tags WHERE id NOT IN ('.implode(',', $rslt_extant).')';
$stmt_del = $pdo->prepare($sql_del);
$stmt_del->execute();*/


////////////////////////////////////////////////////


/*// Delete old columns from artists_tags
$s_col = 'ALTER TABLE artists_tags DROP COLUMN user_agrees';
$t_col = $pdo->prepare($s_col);
$t_col->execute();*/


////////////////////////////////////////////////////


/* // Delete old columns from musicians_tags
$s_col = 'ALTER TABLE musicians_tags DROP COLUMN user_agrees';
$t_col = $pdo->prepare($s_col);
$t_col->execute();*/


////////////////////////////////////////////////////


/*// Delete old columns from releases_tags
$s_col = 'ALTER TABLE releases_tags DROP COLUMN user_agrees';
$t_col = $pdo->prepare($s_col);
$t_col->execute();*/


////////////////////////////////////////////////////


// Change name of mod_agrees
/*$sql[] = '
ALTER TABLE `artists_tags` CHANGE `mod_agrees` `mod_score` TINYINT(1) NULL DEFAULT "0"';

$sql[] = '
ALTER TABLE `musicians_tags` CHANGE `mod_agrees` `mod_score` TINYINT(1) NULL DEFAULT "0"';

$sql[] = '
ALTER TABLE `releases_tags` CHANGE `mod_agrees` `mod_score` TINYINT(1) NULL DEFAULT "0"';*/


////////////////////////////////////////////////////


// Add is_votable column to tags, and add score back to items_tags
/*$sql[] = '
ALTER TABLE `tags_artists` ADD `is_votable` BOOLEAN NOT NULL DEFAULT TRUE AFTER `type`;';

$sql[] = '
ALTER TABLE `tags_musicians` ADD `is_votable` BOOLEAN NOT NULL DEFAULT TRUE AFTER `type`;';

$sql[] = '
ALTER TABLE `tags_releases` ADD `is_votable` BOOLEAN NOT NULL DEFAULT TRUE AFTER `type`;';

$sql[] = '
ALTER TABLE `artists_tags` ADD `score` INT NOT NULL DEFAULT "0" AFTER `user_id`;';

$sql[] = '
ALTER TABLE `musicians_tags` ADD `score` INT NOT NULL DEFAULT "0" AFTER `user_id`;';

$sql[] = '
ALTER TABLE `releases_tags` ADD `score` INT NOT NULL DEFAULT "0" AFTER `user_id`;';*/


////////////////////////////////////////////////////


// Get sum of scores from votes tables and update items_tags tables
/*$sql[] = '
UPDATE
	artists_tags,
	( SELECT SUM(score) AS score, item_id FROM votes_artists_tags GROUP BY votes_artists_tags.item_id ) AS votes
SET
	artists_tags.score=votes.score
WHERE
	artists_tags.id=votes.item_id';

$sql[] = '
UPDATE
	musicians_tags,
	( SELECT SUM(score) AS score, item_id FROM votes_musicians_tags GROUP BY votes_musicians_tags.item_id ) AS votes
SET
	musicians_tags.score=votes.score
WHERE
	musicians_tags.id=votes.item_id';

$sql[] = '
UPDATE
	releases_tags,
	( SELECT SUM(score) AS score, item_id FROM votes_releases_tags GROUP BY votes_releases_tags.item_id ) AS votes
SET
	releases_tags.score=votes.score
WHERE
	releases_tags.id=votes.item_id';*/







