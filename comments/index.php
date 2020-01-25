<?php
$access_comment = $access_comment ?: new access_comment($pdo);
$markdown_parser = $markdown_parser ?: new parse_markdown($pdo);

$page_title = 'Comments';

include('../blog/head.php');

$page = is_numeric($_GET['page']) ? $_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;
$limit_string = $offset.','.$limit;
$comments = $access_comment->access_comment([ 'get_user_likes' => true, 'get' => 'all', 'limit_threads' => true, 'limit' => $limit_string ]);
$num_comments = count($comments);

$sql_total_comments = 'SELECT COUNT(*) AS num_comments FROM comments WHERE thread_id IS NULL';
$stmt_total_comments = $pdo->prepare($sql_total_comments);
$stmt_total_comments->execute();
$num_total_comments = $stmt_total_comments->fetchColumn();
$num_total_pages = $num_total_comments / $limit;

include('../comments/page-index.php');