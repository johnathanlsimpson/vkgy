<?php
	$access_comment = $access_comment ?: new access_comment($pdo);
	$markdown_parser = $markdown_parser ?: new parse_markdown($pdo);
	$comments = $access_comment->access_comment([ 'user_id' => $_SESSION['userID'], 'get' => 'all', 'limit' => 10 ]);
	
	include('../comments/partial-comments.php');
	render_default_comment_section('none', 0, $comments, $markdown_parser);