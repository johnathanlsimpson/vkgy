<?php

include_once('../php/include.php');
$access_user = new access_user($pdo);

$en_id = is_numeric($_POST['en_id']) ? $_POST['en_id'] : null;
$user_id = $_SESSION['user_id'];
$content = sanitize($_POST['content']);
$language = friendly($_POST['language']);
$id;

$allowed_languages = [
	'ja' => '日本語',
	'es' => 'español',
	'fr' => 'français',
	'ru' => 'Русский',
	'zh' => '中文',
];

if($_SESSION['can_add_data']) {
	
	if(is_numeric($en_id) && strlen($content) && in_array($language, array_keys($allowed_languages))) {
		
		$sql_accepted = 'SELECT *, '.$language.'_id AS accepted_id FROM translations WHERE id=? LIMIT 1';
		$stmt_accepted = $pdo->prepare($sql_accepted);
		$stmt_accepted->execute([ $en_id ]);
		$rslt_accepted = $stmt_accepted->fetch();
		
		$sql_check = 'SELECT * FROM translations_proposals WHERE en_id=? AND user_id=? AND language=? LIMIT 1';
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $en_id, $user_id, $language ]);
		$rslt_check = $stmt_check->fetch();
		
		// Editing previous entry
		if(is_array($rslt_check) && !empty($rslt_check)) {
			
			$id = $rslt_check['id'];
			
			$sql_votes = 'SELECT SUM(vote) AS num_votes FROM translations_votes WHERE proposal_id=? GROUP BY proposal_id';
			$stmt_votes = $pdo->prepare($sql_votes);
			$stmt_votes->execute([ $id ]);
			$num_votes = $stmt_votes->fetchColumn();
			
			$sql_edit = 'UPDATE translations_proposals SET content=? WHERE id=? LIMIT 1';
			$stmt_edit = $pdo->prepare($sql_edit);
			
			if($stmt_edit->execute([ $content, $id ])) {
				
				$output['date_occurred'] = substr($rslt_check['date_occurred'], 0, 10);
				$output['num_votes'] = $num_votes;
				$output['is_edit'] = 1;
				$output['status'] = 'success';
				
			}
			
		}
		
		// Adding new proposal
		else {
			
			$sql_add = 'INSERT INTO translations_proposals (en_id, language, content, user_id) VALUES (?, ?, ?, ?)';
			$stmt_add = $pdo->prepare($sql_add);
			
			if($stmt_add->execute([ $en_id, $language, $content, $user_id ])) {
				
				$id = $pdo->lastInsertId();
				
				$sql_vote = 'INSERT INTO translations_votes (user_id, proposal_id, vote) VALUES (?, ?, ?)';
				$stmt_vote = $pdo->prepare($sql_vote);
				$stmt_vote->execute([ $user_id, $id, 1 ]);
				
				$output['date_occurred'] = date('Y-m-d');
				$output['num_votes'] = 1;
				$output['status'] = 'success';
				
				// If no currently accepted answer, auto accept this one... for now!
				if(!is_numeric($rslt_accepted['accepted_id'])) {
					
					// Update string
					$sql_accept = 'UPDATE translations SET '.$language.'_id=? WHERE id=? LIMIT 1';
					$stmt_accept = $pdo->prepare($sql_accept);
					$stmt_accept->execute([ $id, $en_id ]);
					
					// Update accepted ID
					$rslt_accepted['accepted_id'] = $id;
					
					// Regenerate translation file
					generate_translation_file($rslt_accepted['folder'], $language, $pdo);
					
				}
				
			}
			
		}
		
		// Prepare output
		if(is_numeric($id) && $output['status'] === 'success') {
			
			$output['en_id'] = $en_id;
			$output['is_accepted'] = $rslt_accepted['accepted_id'] == $id ? null : 'any--hidden';
			$output['user_icon'] = $_SESSION['icon'];
			$output['user_is_vip'] = $_SESSION['is_vip'];
			$output['user_username'] = $_SESSION['username'];
			$output['id'] = $id;
			$output['username'] = $_SESSION['username'];
			$output['content'] = $content;
			$output['language_name'] = $allowed_languages[$language];
			$output['language'] = $language;
			$output['upvote_is_checked'] = 'checked';
			$output['downvote_is_checked'] = null;
			
		}
		
	}
	
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);