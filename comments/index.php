<?php
$access_comment = $access_comment ?: new access_comment($pdo);
$markdown_parser = $markdown_parser ?: new parse_markdown($pdo);
$comments = $access_comment->access_comment([ 'user_id' => $_SESSION['userID'], 'get' => 'all', 'limit' => 15 ]);

$page_title = 'Comments';

breadcrumbs([
	'Comments' => '/comments/',
]);

include('../comments/page-index.php');