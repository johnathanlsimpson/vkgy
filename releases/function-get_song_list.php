<?php
include_once('../php/include.php');
include_once('../php/function-render_json_list.php');
	
if(is_numeric($_POST['artist_id'])) {
	
	ob_start();
	render_json_list('song', sanitize($_POST['artist_id']));
	$songs_list = ob_get_clean();
	
	$songs_list = preg_replace('/'.'<template.+?>(.*)<\/template>'.'/', '$1', $songs_list);
	echo $songs_list;
	
	
	
	/*$sql_songs = "SELECT name, romaji FROM releases_tracklists WHERE artist_id=? GROUP BY COALESCE(romaji, name) ORDER BY COALESCE(romaji, name) ASC";
	$stmt_songs = $pdo->prepare($sql_songs);
	$stmt_songs->execute([ sanitize($_POST["artist_id"]) ]);
	$songs = $stmt_songs->fetchAll();

	if(is_array($songs) && !empty($songs)) {
		foreach($songs as $key => $song) {
			$song["name"] = clean_song_title($song["name"]);
			$song["romaji"] = clean_song_title($song["romaji"]);
			$songs[$key] = $song;

			$tmp_song = $song["romaji"] ? $song["romaji"]." (".$song["name"].")" : $song["name"];
			$tmp_songs[$tmp_song] = $key;
		}

		if(is_array($tmp_songs) && !empty($tmp_songs)) {
			foreach($tmp_songs as $tmp_song => $key) {
				if($tmp_song) {
					$output[] = [
						"name" => "".$tmp_song
					];
				}
			}
		}
		echo json_encode($output);
	echo '*';
	}*/
}