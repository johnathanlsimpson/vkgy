<?php

include_once('../php/include.php');

// Get data: add/edit interview
if($_GET['action'] === 'update') {
	
	$sql_questions = 'SELECT * FROM interviews_questions ORDER BY romaji ASC';
	$stmt_questions = $pdo->prepare($sql_questions);
	$stmt_questions->execute();
	$rslt_questions = $stmt_questions->fetchAll();
	
	if(is_numeric($_GET['id'])) {
		$sql_interview = '
		SELECT interviews.artist_name, interviews.artist_romaji, interviews_questions.name, interviews_questions.romaji
		FROM interviews 
		LEFT JOIN interviews_tracklists ON interviews_tracklists.interview_id=interviews.id 
		LEFT JOIN interviews_questions ON interviews_questions.id=interviews_tracklists.question_id
		WHERE interviews.id=?';
		$stmt_interview = $pdo->prepare($sql_interview);
		$stmt_interview->execute([ sanitize($_GET['id']) ]);
		$rslt_interview = $stmt_interview->fetchAll();
	}
	
	$view = 'update-interview';
	
}

// Get data: add/edit questions
elseif($_GET['action'] === 'update-questions') {
	
	$sql_questions = 'SELECT * FROM interviews_questions ORDER BY romaji ASC';
	$stmt_questions = $pdo->prepare($sql_questions);
	$stmt_questions->execute();
	$rslt_questions = $stmt_questions->fetchAll();
	
	$view = 'update-questions';
	
}

// Show: add/edit interview
if($view === 'update-interview') {
	include('page-update.php');
}
elseif($view === 'update-questions') {
	include('page-update-questions.php');
}