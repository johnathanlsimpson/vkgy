<?php

$access_user = new access_user($pdo);

// Get translation strings
$sql_translations = 'SELECT translations.* FROM translations';
$stmt_translations = $pdo->prepare($sql_translations);
$stmt_translations->execute();
$strings = $stmt_translations->fetchAll();

// Get proposed translations
$sql_proposals = '
SELECT translations_proposals.*, SUM(translations_votes.vote) AS num_votes 
FROM translations_proposals 
LEFT JOIN translations_votes ON translations_votes.proposal_id=translations_proposals.id
GROUP BY translations_proposals.id
ORDER BY en_id ASC, language ASC, date_occurred DESC';
$stmt_proposals = $pdo->prepare($sql_proposals);
$stmt_proposals->execute();
$rslt_proposals = $stmt_proposals->fetchAll();

// Get proposals' users
if(is_array($rslt_proposals) && !empty($rslt_proposals)) {
	foreach($rslt_proposals as $proposal_key => $proposal) {
		$proposal['user'] = $access_user->access_user([ 'id' => $proposal['user_id'], 'get' => 'name' ]);
		$proposals[$proposal['en_id']][] = $proposal;
	}
}

// Get votes of user who's viewing page
if($_SESSION['is_signed_in']) {
	$sql_votes = 'SELECT * FROM translations_votes WHERE user_id=?';
	$stmt_votes = $pdo->prepare($sql_votes);
	$stmt_votes->execute([ $_SESSION['user_id'] ]);
	$rslt_votes = $stmt_votes->fetchAll();
	
	// Transform votes into upvote/downvote arrays
	if(is_array($rslt_votes) && !empty($rslt_votes)) {
		foreach($rslt_votes as $vote) {
			if($vote['vote'] > 0) {
				$user_upvotes[] = $vote['proposal_id'];
			}
			else {
				$user_downvotes[] = $vote['proposal_id'];
			}
		}
	}
}

// Allowed sections
$sections = [
	'404',
	'account',
	'artists',
	'avatar',
	'badges',
	'blog',
	'comments',
	'database',
	'documentation',
	'errors',
	'images',
	'interview',
	'labels',
	'lives',
	'magazines',
	'main',
	'musicians',
	'php',
	'releases',
	'search',
	'support',
	'translations'
];

if($_SESSION['is_vip']) {
	include('../translations/page-index.php');
}

?>