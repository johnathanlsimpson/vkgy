<?php

include_once('../php/include.php');

$allowed_types = ['artist', 'label', 'musician', 'release', 'user'];

if(in_array($_GET['type'], $allowed_types)) {
	if($_GET['type'] === 'user') {
		$sql_search = 'SELECT username FROM users WHERE username LIKE CONCAT("%", ?, "%") ORDER BY username ASC LIMIT 10';
		$stmt_search = $pdo->prepare($sql_search);
		$stmt_search->execute([ friendly($_GET['q']) ]);
		$rslt_search = $stmt_search->fetchAll();
		
		foreach($rslt_search as $rslt) {
			$output[] = [
				'',
				'',
				$rslt['username']
			];
		}
	}
	else {
		$sql_search = 'SELECT id, name, romaji, friendly FROM '.$_GET['type'].'s WHERE name LIKE CONCAT("%", ?, "%") OR romaji LIKE CONCAT("%", ?, "%") OR friendly LIKE CONCAT("%", ?, "%") ORDER BY friendly ASC LIMIT 50';
		$stmt_search = $pdo->prepare($sql_search);
		$stmt_search->execute([ sanitize($_GET['q']), sanitize($_GET['q']), friendly($_GET['q']) ]);
		$rslt_search = $stmt_search->fetchAll();

		foreach($rslt_search as $rslt) {
			$output[] = [
				$rslt['id'],
				$rslt['friendly'],
				($rslt['romaji'] ?: $rslt['name']).($rslt['romaji'] ? ' ('.$rslt['name'].')' : null).($rslt['friendly'] != friendly($rslt['romaji'] ?: $rslt['name']) ? ' ('.$rslt['friendly'].')' : null)
			];
		}
	}
}

echo json_encode($output);