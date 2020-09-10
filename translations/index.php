<?php

$access_user = new access_user($pdo);

// Get allowed sections
$sql_sections = 'SELECT translations.folder AS section FROM translations GROUP BY section ORDER BY section ASC';
$stmt_sections = $pdo->prepare($sql_sections);
$stmt_sections->execute();
$rslt_sections = $stmt_sections->fetchAll(PDO::FETCH_COLUMN);

// Adjust main query to get specified section
if(strlen($_GET['section']) && in_array($_GET['section'], $rslt_sections)) {
	$section_name = friendly($_GET['section']);
}
else {
	$section_name = 'UI';
}

// Get translation strings
$sql_translations = 'SELECT translations.* FROM translations ORDER BY translations.folder ASC, translations.content ASC';
$stmt_translations = $pdo->prepare($sql_translations);
$stmt_translations->execute($values_translations);
$strings = $stmt_translations->fetchAll();

// Loop through strings and get list of sections, also replace {}
foreach($strings as $string_key => $string) {
	$strings[$string_key]['content'] = str_replace(['{','}'], ['<span class="any__note">&#123;','&#125;</span>'], $string['content']);
	$sections[ $string['folder'] ] = '';
}
$sections = array_keys($sections);

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

// Get proposals' users and replace tokens
if(is_array($rslt_proposals) && !empty($rslt_proposals)) {
	foreach($rslt_proposals as $proposal_key => $proposal) {
		$proposal['content'] = str_replace(['{','}'], ['<span class="any__note">&#123;', '&#125;</span>'], $proposal['content']);
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
/*$sections = [
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
];*/

if($_SESSION['is_editor']) {
	include('../translations/page-index.php');
}

?>