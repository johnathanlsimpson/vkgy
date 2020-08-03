<?php

include_once('../php/include.php');

$id = is_numeric($_POST['id']) ? $_POST['id'] : null;
$vote_type = in_array($_POST['vote_type'], ['upvote', 'downvote']) ? $_POST['vote_type'] : null;
$vote = $vote_type === 'upvote' ? 1 : -1;
$is_selected = $_POST['is_selected'] ? true : false;
$user_id = $_SESSION['user_id'];

if($is_selected) {
	$action = 'add';
}
else {
	$action = 'remove';
}

if($_SESSION['is_signed_in']) {
	
	if(is_numeric($id) && $vote_type) {
		
		$sql_check = 'SELECT id FROM translations_votes WHERE proposal_id=? AND user_id=? LIMIT 1';
		$stmt_check = $pdo->prepare($sql_check);
		$stmt_check->execute([ $id, $_SESSION['user_id'] ]);
		$vote_id = $stmt_check->fetchColumn();
		
		$sql_num = 'SELECT SUM(vote) AS num_votes FROM translations_votes WHERE proposal_id=? GROUP BY proposal_id';
		$stmt_num = $pdo->prepare($sql_num);
		$stmt_num->execute([ $id ]);
		$num_votes = $stmt_num->fetchColumn();
		
		// If vote already exists
		if(is_numeric($vote_id)) {
			
			// Update extant vote
			if($action === 'add') {
				$sql_update = 'UPDATE translations_votes SET vote=? WHERE id=? LIMIT 1';
				$values_update = [ $vote, $vote_id ];
				$num_votes = $num_votes + $vote * 2;
			}
			
			// Remove extant vote
			else {
				$sql_update = 'DELETE FROM translations_votes WHERE id=? LIMIT 1';
				$values_update = [ $vote_id ];
				$num_votes = $num_votes - $vote;
			}
			$stmt_update = $pdo->prepare($sql_update);
			
			if($stmt_update->execute($values_update)) {
				$output['num_votes'] = $num_votes;
				$output['status'] = 'success';
			}
			
		}
		
		// If vote doesn't exist
		else {
			
			if($action === 'add') {
				$sql_new = 'INSERT INTO translations_votes (proposal_id, vote, user_id) VALUES (?, ?, ?)';
				$stmt_new = $pdo->prepare($sql_new);
				
				if($stmt_new->execute([ $id, $vote, $_SESSION['user_id'] ])) {
					$output['num_votes'] = $num_votes + $vote;
					$output['status'] = 'success';
				}
				
			}
			
		}
		
		// Given vote output, decide if translation is new accepted
		if(is_numeric($output['num_votes'])) {
			
			$num_votes = $output['num_votes'];
			
			// Update string to use most voted proposal (if votes for it are positive)
			$sql_rank = '
			
			SELECT
				SUM(votes.vote) AS num_votes, votes.proposal_id, proposal.language, proposal.en_id
			FROM
				translations_votes vote
			LEFT JOIN
				translations_proposals proposal ON proposal.id=vote.proposal_id
			LEFT JOIN
				translations_proposals proposals ON proposals.en_id=proposal.en_id AND proposals.language=proposal.language
			LEFT JOIN
				translations_votes votes ON votes.proposal_id=proposals.id
			WHERE
				vote.proposal_id=?
			GROUP BY
				votes.proposal_id
			ORDER BY
				num_votes DESC
			
			';
			$stmt_rank = $pdo->prepare($sql_rank);
			$stmt_rank->execute([ $id ]);
			$rslt_rank = $stmt_rank->fetch();
			$accepted_proposal_id = $rslt_rank['num_votes'] > 0 ? $rslt_rank['proposal_id'] : null;
			
			$sql_string = 'UPDATE translations SET '.$rslt_rank['language'].'_id=? WHERE id=? LIMIT 1';
			$stmt_string = $pdo->prepare($sql_string);
			$stmt_string->execute([ $accepted_proposal_id, $rslt_rank['en_id'] ]);
			
			$output['accepted_id'] = $accepted_proposal_id;
			$output['is_accepted'] = $id == $accepted_proposal_id ? 1 : 0;
			
		}
		
	}
	
}

$output['status'] = $output['status'] ?: 'error';
echo json_encode($output);