<?php

include_once('../php/include.php');
include_once('../php/class-views.php');
$views = new views($pdo);

$youtube_id = sanitize($_GET['id']);

if( strlen($youtube_id) ) {
	
	$sql_id = 'SELECT id FROM videos WHERE youtube_id=? LIMIT 1';
	$stmt_id = $pdo->prepare($sql_id);
	$stmt_id->execute([ $youtube_id ]);
	$id = $stmt_id->fetchColumn();
	
	if( is_numeric($id) ) {
		$views->add('video', $id);
	}
	
}