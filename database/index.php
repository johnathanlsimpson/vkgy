<?php
	
// ======================================================
// Database front page
// ======================================================
$sql_recent = "
	SELECT recent.*, users.username
	FROM (
		(
			SELECT
				'artist' AS type, edits_artists.user_id, CONCAT_WS('/', '', 'artists', friendly, '') AS url, COALESCE(romaji, name) AS quick_name, edits_artists.date_occurred AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_artists
			INNER JOIN (
				SELECT MAX(id) AS id FROM edits_artists GROUP BY artist_id ORDER BY id DESC LIMIT 20
			) max_edit_artist_ids
			ON edits_artists.id=max_edit_artist_ids.id
			LEFT JOIN artists ON artists.id=edits_artists.artist_id
		)
		UNION
		(
			SELECT
				'company' AS type, edits_labels.user_id, CONCAT_WS('/', '', 'labels', friendly, '') AS url, COALESCE(romaji, name) AS quick_name, edits_labels.date_occurred AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_labels
			INNER JOIN (
				SELECT MAX(id) AS id FROM edits_labels GROUP BY label_id ORDER BY id DESC LIMIT 20
			) max_edit_label_ids
			ON edits_labels.id=max_edit_label_ids.id
			LEFT JOIN labels ON labels.id=edits_labels.label_id
		)
		UNION
		(
			SELECT
				'musician' AS type,
				edits_musicians.user_id,
				CONCAT_WS('/', '', 'musicians', musicians.id, musicians.friendly, '') AS url, COALESCE(romaji, name) AS quick_name, edits_musicians.date_occurred AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_musicians
			INNER JOIN (
				SELECT MAX(id) AS id FROM edits_musicians GROUP BY musician_id ORDER BY id DESC LIMIT 20
			) max_edit_musician_ids
			ON edits_musicians.id=max_edit_musician_ids.id
			LEFT JOIN musicians ON musicians.id=edits_musicians.musician_id
		)
		UNION
		(
			SELECT
				'release' AS type,
				edits_releases.user_id,
				CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url,
				CONCAT_WS(' ', COALESCE(releases.romaji, releases.name, ''), COALESCE(press_romaji, press_name, ''), COALESCE(type_romaji, type_name, '')) AS quick_name,
				edits_releases.date_occurred AS date_edited,
				COALESCE(artists.romaji, artists.name) AS artist_quick_name,
				CONCAT_WS('/', '', 'artists', artists.friendly, '') AS artist_url
			FROM edits_releases
			INNER JOIN (
				SELECT MAX(id) AS id FROM edits_releases GROUP BY release_id ORDER BY id DESC LIMIT 20
			) max_edit_release_ids
			ON edits_releases.id=max_edit_release_ids.id
			LEFT JOIN releases ON releases.id=edits_releases.release_id
			LEFT JOIN artists ON artists.id=releases.artist_id
		)
	) AS recent
	LEFT JOIN users ON users.id=recent.user_id
	ORDER BY recent.type ASC, recent.date_edited DESC
";
$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute();
$database = $stmt_recent->fetchAll();

$page_title = 'Database';
$page_header = 'Database';

subnav([
	'Artists' => '/artists/',
	'Musicians' => '/musicians/',
	'Labels' => '/labels/',
	'Releases' => '/releases/',
	'Lives' => '/lives/',
	'Images' => '/images/',
]);

include("../database/page-index.php");