<?php
	
// ======================================================
// Database front page
// ======================================================
$sql_recent = "
	SELECT recent.*, users.username
	FROM (
		(
			SELECT 'artist' AS type, edits_artists.user_id, CONCAT_WS('/', '', 'artists', friendly, '') AS url, COALESCE(romaji, name) AS quick_name, MAX(edits_artists.date_occurred) AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_artists
			LEFT JOIN artists ON artists.id=edits_artists.artist_id
			GROUP BY edits_artists.artist_id
			ORDER BY MAX(edits_artists.date_occurred) DESC LIMIT 20
		)
		UNION
		(
			SELECT 'company' AS type, edits_labels.user_id, CONCAT_WS('/', '', 'labels', friendly, '') AS url, COALESCE(romaji, name) AS quick_name, MAX(edits_labels.date_occurred) AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_labels
			LEFT JOIN labels ON labels.id=edits_labels.label_id
			GROUP BY edits_labels.label_id
			ORDER BY MAX(edits_labels.date_occurred) DESC LIMIT 20
		)
		UNION
		(
			SELECT 'musician' AS type, edits_musicians.user_id, CONCAT_WS('/', '', 'musicians', musicians.id, musicians.friendly, '') AS url, COALESCE(romaji, name) AS quick_name, MAX(edits_musicians.date_occurred) AS date_edited, '' AS artist_quick_name, '' AS artist_url
			FROM edits_musicians
			LEFT JOIN musicians ON musicians.id=edits_musicians.musician_id
			GROUP BY edits_musicians.musician_id
			ORDER BY MAX(edits_musicians.date_occurred) DESC LIMIT 20
		)
		UNION
		(
			SELECT 'release' AS type, edits_releases.user_id, CONCAT_WS('/', '', 'releases', artists.friendly, releases.id, releases.friendly, '') AS url, CONCAT_WS(' ', COALESCE(releases.romaji, releases.name, ''), COALESCE(press_romaji, press_name, ''), COALESCE(type_romaji, type_name, '')) AS quick_name, MAX(edits_releases.date_occurred) AS date_edited, COALESCE(artists.romaji, artists.name) AS artist_quick_name, CONCAT_WS('/', '', 'artists', artists.friendly, '') AS artist_url
			FROM edits_releases
			LEFT JOIN releases ON releases.id=edits_releases.release_id
			LEFT JOIN artists ON artists.id=releases.artist_id
			GROUP BY edits_releases.release_id
			ORDER BY MAX(edits_releases.date_occurred) DESC LIMIT 20
		)
	) AS recent
	LEFT JOIN users ON users.id=recent.user_id
	ORDER BY recent.type ASC, recent.date_edited DESC
";
$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute();
$database = $stmt_recent->fetchAll();

include_once("../database/head.php");
include("../database/page-index.php");

$pageTitle = 'Database';