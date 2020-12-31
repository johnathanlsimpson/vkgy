<?php

$markdown_parser = new parse_markdown($pdo);
$access_comment = new access_comment($pdo);

$page_header = lang('Development', 'ロードマップ', 'div');

subnav([
	'Latest update' => '/development/',
	'All updates' => '/development/all/',
	'Issues' => '/development/issues/',
]);

$template = $_GET['template'];
$id = is_numeric($_GET['id']) ? $_GET['id'] : null;

//
// Get issues
//
if( $template === 'issues' || $template === 'index' ) {
	
	$sql_issues = '
		SELECT issues.id, issues.title, issues.is_completed, issues.issue_type, issues.date_occurred, issues.user_id, IF(votes_development.score, SUM(votes_development.score), 0) AS score
		FROM development issues
		LEFT JOIN votes_development ON votes_development.item_id=issues.id
		WHERE issues.is_issue=?
		GROUP BY issues.id
		ORDER BY score DESC, issues.date_occurred DESC
	';
	$stmt_issues = $pdo->prepare($sql_issues);
	$stmt_issues->execute([ 1 ]);
	$issues = $stmt_issues->fetchAll();
	$num_issues = count($issues);
	
	// If no issues, redirect to index
	if( $template === 'issues' && !$num_issues ) {
		$template = 'index';
		$error = 'No issues found. Showing development index instead.';
	}
	
	// Split issues by completion status
	else {
		
		foreach($issues as $issue_key => $issue) {
			
			if($issue['is_completed']) {
				$tmp_issues['completed'][] = $issue;
			}
			else {
				$tmp_issues['incomplete'][] = $issue;
			}
			
		}
		
		$issues = $tmp_issues;
		
	}
	
}

//
// Get entry
//
if( is_numeric($id) || $template === 'index' ) {

	$values_entry = [ is_numeric($id) ? $id : 0 ];
	$sql_entry = 'SELECT *, SUBSTRING(date_occurred, 1, 10) AS date_occurred FROM development WHERE '.( is_numeric($id) ? 'id=?' : 'is_issue=? OR is_issue IS NULL' ).' ORDER BY development.date_occurred DESC LIMIT 1';
	$stmt_entry = $pdo->prepare($sql_entry);
	$stmt_entry->execute($values_entry);
	$entry = $stmt_entry->fetch();
	
	// Get additional info
	if( is_array($entry) && !empty($entry) ) {
		
		// Get entry's user
		$entry['user'] = $access_user->access_user([ 'id' => $entry['user_id'], 'get' => 'name' ]);
		
		// If regular dev entry, do some additional formatting
		if( !$entry['is_issue'] && $template != 'update' ) {
			
			$content = $entry['content'];
			
			// Parse entry
			$content = $markdown_parser->parse_markdown($content);
			
			// Format change type
			$content = preg_replace_callback('/'.'\[(Addition|Bug fix|Change|Feature)\]'.'/', function($matches) {
				return '<span class="any__note">'.strtolower($matches[1]).'</span>';
			}, $content);
			
			// Format affected folder
			$content = preg_replace('/'.'\<code\>([A-z\.]+)\<\/code\>'.'/', '<span class="entry__affects any--weaken">$1</span>', $content);
			
			$entry['content'] = $content;
			
		}
		
	}
	
	// If no entry, redirect to index
	else {
		$template = 'index';
		$error = 'No entry was found.';
	}
	
}

//
// Get past entries
//
if( $template === 'all' ) {
	
	$sql_entries = 'SELECT title, id, SUBSTRING(development.date_occurred, 1, 10) AS date_occurred FROM development WHERE is_issue=? OR is_issue IS NULL ORDER BY date_occurred DESC';
	$stmt_entries = $pdo->prepare($sql_entries);
	$stmt_entries->execute([ 0 ]);
	$entries = $stmt_entries->fetchAll();
	
	// Reorder entries by date (year and month descending, day ascending)
	foreach($entries as $entry) {
		
		list($year, $month, $day) = explode( '-', $entry['date_occurred'] );
		$tmp_entries[$year][$month] = $tmp_entries[$year][$month] ?: [];
		array_unshift($tmp_entries[$year][$month], $entry);
		
	}
	
	$entries = $tmp_entries;
	unset($tmp_entries);
	
}

// ==============================================

//
// Display issues
//
if( $template === 'issues' ) {
	
	// Format issues
	foreach(['completed', 'incomplete'] as $key) {
		foreach($issues[$key] as $issue_key => $issue) {
			
			// Get user
			$issues[$key][$issue_key]['user'] = $access_user->access_user([ 'id' => $issue['user_id'], 'get' => 'name' ]);
			
		}
	}
	
	include('../development/page-issues.php');
	
}

//
// Display entry
//
elseif( $template === 'entry' ) {
	
	// Get comments
	$entry['comments'] = $access_comment->access_comment([ 'id' => $entry['id'], 'type' => 'development', 'get' => 'all' ]);
	
	// Wrap side-by-side images into gallery
	$gallery_pattern = '((?:<div class="module module--image.*?<\/div>\n?){2,})';
	$entry['content'] = preg_replace(
		'/'.$gallery_pattern.'/',
		"<div class=\"module--gallery-wrapper\"><div class=\"module--gallery any--scrollbar\">\n$1</div></div>\n",
		$entry['content']
	);
	
	include('../development/page-entry.php');
	
}

//
// Display update page
//
elseif( $template === 'update' ) {
	
	include('../development/page-update.php');
	
}

//
// Display past updates
//
elseif( $template === 'all' ) {
	
	include('../development/page-all.php');
	
}

//
// Display index
//
else {
	
	// Wrap side-by-side images into gallery
	$gallery_pattern = '((?:<div class="module module--image.*?<\/div>\n?){2,})';
	$entry['content'] = preg_replace(
		'/'.$gallery_pattern.'/',
		"<div class=\"module--gallery-wrapper\"><div class=\"module--gallery any--scrollbar\">\n$1</div></div>\n",
		$entry['content']
	);
	
	// Limit number of issues
	$issues = $issues['incomplete'] ? array_slice($issues['incomplete'], 0, 5) : null;
	
	include('../development/page-index.php');

}