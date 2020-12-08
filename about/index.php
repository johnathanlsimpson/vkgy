<?php

$markdown_parser = new parse_markdown($pdo);
$access_comment = new access_comment($pdo);

$page_header = lang('About vkgy', 'vkgyについて', 'div');

subnav([
	'Site updates' => '/about/development/',
	'About' => '/about/#about',
	'Contact' => '/about/#contact',
	'Privacy policy' => '/about/#privacy',
]);

$allowed_templates = [
	'about' => 'index',
	'contact' => 'contact',
	'privacy-policy' => 'privacy',
	'development' => 'development',
	'update' => 'update',
];

$template = $_GET['template'];
$id = is_numeric($_GET['id']) ? $_GET['id'] : null;

if( in_array($template, array_keys($allowed_templates)) ) {
	
	if( $template === 'development' || $template === 'update' ) {
		
		// View development entry
		if( is_numeric($id) ) {
			
			$sql_entry = 'SELECT development.*, SUBSTRING(development.date_occurred, 1, 10) AS date_occurred FROM development WHERE development.id=? LIMIT 1';
			$stmt_entry = $pdo->prepare($sql_entry);
			$stmt_entry->execute([ $id ]);
			$entry = $stmt_entry->fetch();
			
			if( is_array($entry) && !empty($entry) ) {
				
				$entry['user'] = $access_user->access_user([ 'id' => $entry['user_id'], 'get' => 'name' ]);
				
				// Update entry
				if( $template === 'update' ) {
					
					include('../about/page-update.php');
					
				}
				
				// View entry
				else {
					
					$entry['comments'] = $access_comment->access_comment([ 'id' => $entry['id'], 'type' => 'development', 'get' => 'all' ]);
					
					include('../about/page-entry.php');
					
				}
				
			}
			
		}
		
		// Development home
		else {
			
			$sql_entries = 'SELECT id, title, friendly, SUBSTRING(date_occurred,1,10) AS date_occurred, content FROM development WHERE is_issue=? ORDER BY date_occurred DESC LIMIT 10';
			$stmt_entries = $pdo->prepare($sql_entries);
			$stmt_entries->execute([ 0 ]);
			$entries = $stmt_entries->fetchAll();
			
			$entries[0]['content'] = substr( strip_tags( $markdown_parser->parse_markdown($entries[0]['content']) ), 0, 300 );
			
			$sql_issues = 'SELECT id, title, is_completed FROM development WHERE is_issue=? AND (is_completed IS NULL OR is_completed < 1) ORDER BY date_occurred DESC';
			$stmt_issues = $pdo->prepare($sql_issues);
			$stmt_issues->execute([ 1 ]);
			$issues = $stmt_issues->fetchAll();
			
			include('../about/page-development.php');
			
		}
		
	}
	else {
		
		include('../about/page-index.php');
		
	}
	
}