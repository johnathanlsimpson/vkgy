<?php

include_once('../php/include.php');

$proposal_id = is_numeric($_POST['id']) ? $_POST['id'] : null;
$vote_type = in_array($_POST['vote_type'], ['upvote', 'downvote']) ? $_POST['vote_type'] : null;
$vote_value = $vote_type === 'upvote' ? 1 : -1;
$is_selected = $_POST['is_selected'] ? true : false;
$user_id = $_SESSION['user_id'];

if($is_selected) {
	$action = 'add';
}
else {
	$action = 'remove';
}

if($_SESSION['is_signed_in']) {
	
	if(is_numeric($proposal_id) && $vote_type) {
		
		// Get vote
		$sql_vote = 'SELECT id FROM translations_votes WHERE proposal_id=? AND user_id=? LIMIT 1';
		$stmt_vote = $pdo->prepare($sql_vote);
		$stmt_vote->execute([ $proposal_id, $_SESSION['user_id'] ]);
		$vote_id = $stmt_vote->fetchColumn();
		
		// Get proposal
		$sql_proposal = '
		SELECT translations.*, translations_proposals.language, translations_proposals.en_id 
		FROM translations_proposals 
		LEFT JOIN translations ON translations.id=translations_proposals.en_id 
		WHERE translations_proposals.id=? 
		LIMIT 1';
		$stmt_proposal = $pdo->prepare($sql_proposal);
		$stmt_proposal->execute([ $proposal_id ]);
		$rslt_proposal = $stmt_proposal->fetch();
		
		/*$sql_num = 'SELECT SUM(vote) AS num_votes FROM translations_votes WHERE proposal_id=? GROUP BY proposal_id';
		$stmt_num = $pdo->prepare($sql_num);
		$stmt_num->execute([ $proposal_id ]);
		$num_votes = $stmt_num->fetchColumn();*/
		
		// If vote already exists
		if(is_numeric($vote_id) && is_numeric($rslt_proposal['id'])) {
			
			// Update extant vote
			if($action === 'add') {
				$sql_update = 'UPDATE translations_votes SET vote=? WHERE id=? LIMIT 1';
				$values_update = [ $vote_value, $vote_id ];
			}
			
			// Remove extant vote
			else {
				$sql_update = 'DELETE FROM translations_votes WHERE id=? LIMIT 1';
				$values_update = [ $vote_id ];
			}
			
			$stmt_update = $pdo->prepare($sql_update);
			
			if($stmt_update->execute($values_update)) {
				$output['status'] = 'success';
			}
			
		}
		
		// If vote doesn't exist
		else {
			
			if($action === 'add') {
				$sql_new = 'INSERT INTO translations_votes (proposal_id, vote, user_id) VALUES (?, ?, ?)';
				$stmt_new = $pdo->prepare($sql_new);
				
				if($stmt_new->execute([ $proposal_id, $vote_value, $_SESSION['user_id'] ])) {
					$output['status'] = 'success';
				}
				
			}
			
		}
		
		// Given vote output, decide if translation is new accepted
		if($output['status'] === 'success') {
			
			$sql_rank = '
			
			SELECT
				COALESCE(SUM(translations_votes.vote),0) AS num_votes, translations_votes.proposal_id, translations_proposals.language, translations.*
			FROM
				translations_proposals
			LEFT JOIN
				translations_votes ON translations_votes.proposal_id=translations_proposals.id
			LEFT JOIN
				translations ON translations.id=translations_proposals.en_id
			WHERE
				translations_proposals.en_id=? AND translations_proposals.language=?
			GROUP BY
				translations_proposals.id
			ORDER BY
				num_votes DESC
				
			';
			$stmt_rank = $pdo->prepare($sql_rank);
			$stmt_rank->execute([ $rslt_proposal['en_id'], $rslt_proposal['language'] ]);
			$rslt_rank = $stmt_rank->fetchAll();
			
			// If winner, do stuff
			if(is_array($rslt_rank) && !empty($rslt_rank)) {
				
				// Get potential winner and set string to use it
				if(is_array($rslt_rank[0]) && !empty($rslt_rank[0])) {
					
					$top_proposal = $rslt_rank[0];
					
					// Make sure so-called top proposal has positive votes
					if($top_proposal['num_votes'] > 0) {
						
						// Return whether or not top proposal is the one that we just voted on
						$output['accepted_id'] = $top_proposal['proposal_id'];
						$output['is_accepted'] = $top_proposal['proposal_id'] == $proposal_id ? 1 : 0;
						
						// If top proposal isn't currently set for the accepted translation, do so
						$sql_accept = 'UPDATE translations SET '.$top_proposal['language'].'_id=? WHERE id=? LIMIT 1';
						$stmt_accept = $pdo->prepare($sql_accept);
						$stmt_accept->execute([ $top_proposal['proposal_id'], $rslt_proposal['en_id'] ]);
						
					}
					
				}
				
				// Walk through vote numbers and get num votes for proposal which is being voted on
				foreach($rslt_rank as $rank) {
					if($rank['proposal_id'] == $proposal_id) {
						$output['num_votes'] = $rank['num_votes'];
						break;
					}
				}
				
			}
			
			// If no winner, make sure none is set on string
			if(!is_numeric($output['accepted_id'])) {
				
				// Return that there's no accepted answer
				$output['accepted_id'] = null;
				$output['is_accepted'] = 0;
				
				// Update translation such that there's no accepted answer for this language
				$sql_reject = 'UPDATE translations SET '.$rslt_proposal['language'].'_id=? WHERE id=? LIMIT 1';
				$stmt_reject = $pdo->prepare($sql_reject);
				$stmt_reject->execute([ null, $rslt_proposal['en_id'] ]);
				
			}
			
			// Update translations for that particular page
			function generate_translation_file($folder, $language, $pdo) {
				
				if(strlen($folder) && file_exists('../'.$folder)) {
					
					$sql_translations = '
						SELECT
							translations.*,
							translations_proposals.content AS translation
						FROM
							translations
						LEFT JOIN
							translations_proposals ON translations_proposals.id=translations.'.$language.'_id
						WHERE
							translations.folder=? AND translations.'.$language.'_id IS NOT NULL
					';
					$stmt_translations = $pdo->prepare($sql_translations);
					$stmt_translations->execute([ $folder ]);
					$rslt_translations = $stmt_translations->fetchAll();
					
					$translations = [];
					
					if(is_array($rslt_translations) && !empty($rslt_translations)) {
						foreach($rslt_translations as $translation) {
							$translations[ $translation['content'] ] = $translation['translation'];
						}
					}
					
					$translation_file = gzcompress( serialize( $translations ) );
					$filename = '../'.$folder.'/lang.'.$language;
					file_put_contents( $filename, $translation_file );
					
				}
				
			}
			generate_translation_file('translations', $rslt_proposal['language'], $pdo);
			
			$output['language'] = $rslt_proposal['language'];
			
		}
		
	}
	
}

$output['num_votes'] = is_numeric($output['num_votes']) ? $output['num_votes'] : 0;
$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);