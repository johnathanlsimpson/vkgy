<?php

include_once('../php/include.php');

$sql[] = '
CREATE TABLE development SELECT title, content, user_id, date_occurred, friendly FROM vip WHERE vip.date_occurred>"2018-07-07"';

$sql[] = '
ALTER TABLE `development` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)';

$sql[] = '
ALTER TABLE `development` ADD `is_issue` BOOLEAN NOT NULL DEFAULT FALSE AFTER `friendly`, ADD `is_completed` BOOLEAN NULL DEFAULT NULL AFTER `is_issue`';

$sql[] = '
ALTER TABLE `development` ADD `issue_type` TINYINT NULL DEFAULT NULL AFTER `is_completed`';

$sql_comments = 'SELECT comments.id, development.* AS item_id FROM comments LEFT JOIN vip ON vip.id=comments.item_id LEFT JOIN development ON development.friendly=vip.friendly WHERE comments.item_type=?';
$stmt_comments = $pdo->prepare($sql_comments);
$stmt_comments->execute([ 2 ]);
foreach($stmt_comments->fetchAll() as $comment) {
	
	$sql_update_comment = 'UPDATE comments SET item_id=? WHERE id=? LIMIT 1';
	$stmt_update_comment = $pdo->prepare($sql_update_comment);
	$stmt_update_comment->execute([ $comment['item_id'], $comment['id'] ]);
	
}