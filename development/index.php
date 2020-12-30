<?php

$markdown_parser = new parse_markdown($pdo);
$access_comment = new access_comment($pdo);

$page_header = lang('Development', 'ロードマップ', 'div');

subnav([
	'Development' => '/development/',
	'Issues' => '/development/issues/',
]);

$template = $_GET['template'];
$id = is_numeric($_GET['id']) ? $_GET['id'] : null;

//
// Get issues
//
if( $template === 'issues' || $template === 'index' ) {
	
	$sql_issues = '
		SELECT development.id, development.title, development.is_completed, IF(votes_development.score, SUM(votes_development.score), 0) AS score
		FROM development
		LEFT JOIN votes_development ON votes_development.item_id=development.id
		WHERE development.is_issue=? AND (development.is_completed IS NULL OR development.is_completed=?)
		GROUP BY development.id
		ORDER BY score DESC, development.date_occurred DESC
	';
	$stmt_issues = $pdo->prepare($sql_issues);
	$stmt_issues->execute([ 1, 0 ]);
	$issues = $stmt_issues->fetchAll();
	$num_issues = count($issues);
	
	// If no issues, and trying to view issues page, redirect to index
	if( !$num_issues ) {
		$template = 'index';
		$error = 'No issues found. Showing development index instead.';
	}
	
}

//
// Get entry
//
if( is_numeric($id) ) {

	$sql_entry = 'SELECT development.*, SUBSTRING(development.date_occurred, 1, 10) AS date_occurred FROM development WHERE development.id=? LIMIT 1';
	$stmt_entry = $pdo->prepare($sql_entry);
	$stmt_entry->execute([ $id ]);
	$entry = $stmt_entry->fetch();
	
	// Get additional info
	if( is_array($entry) && !empty($entry) ) {
		
		// Get entry's user
		$entry['user'] = $access_user->access_user([ 'id' => $entry['user_id'], 'get' => 'name' ]);
		
		// If regular dev entry, do some additional formatting
		if( !$entry['is_issue'] ) {
			
			// Format change type
			$content = preg_replace_callback('/'.'\[(Addition|Bug fix|Change|Feature)\]'.'/', function($matches) {
				return '<span class="any__note">'.strtolower($matches[1]).'</span>';
			}, $content);
			
			// Format affected folder
			$content = preg_replace('/'.'\<code\>([A-z]+)\<\/code\>'.'/', '<span class="any--weaken">$1</span>', $content);
			
		}
		
	}
	
	// If no entry, redirect to index
	else {
		$template = 'index';
	}
	
}

//
// Display issues
//
if( $template === 'issues' ) {
	
	include('../development/page-issues.php');
	
}

//
// Display entry
//
elseif( $template === 'entry' ) {
	
	// Get comments
	$entry['comments'] = $access_comment->access_comment([ 'id' => $entry['id'], 'type' => 'development', 'get' => 'all' ]);
	
	// Parse entry
	$entry['content'] = $markdown_parser->parse_markdown($entry['content']);
	
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
// Display index
//
else {
	
	// Get latest entry
	$sql_entry = 'SELECT id, title, friendly, content, SUBSTRING(date_occurred,1,10) AS date_occurred FROM development WHERE is_issue=? ORDER BY date_occurred DESC LIMIT 1';
	$stmt_entry = $pdo->prepare($sql_entry);
	$stmt_entry->execute([ 0 ]);
	$entry = $stmt_entry->fetch();
	
	// Get user
	$entry['user'] = $access_user->access_user([ 'id' => $entry['user_id'], 'get' => 'name' ]);
	
	// Parse entry
	$entry['content'] = $markdown_parser->parse_markdown($entry['content']);
	
	// Wrap side-by-side images into gallery
	$gallery_pattern = '((?:<div class="module module--image.*?<\/div>\n?){2,})';
	$entry['content'] = preg_replace(
		'/'.$gallery_pattern.'/',
		"<div class=\"module--gallery-wrapper\"><div class=\"module--gallery any--scrollbar\">\n$1</div></div>\n",
		$entry['content']
	);
	
	include('../development/page-index.php');

}