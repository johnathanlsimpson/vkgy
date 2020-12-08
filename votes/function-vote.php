<?php

include_once('../php/include.php');
include_once('../php/class-vote.php');

$vote = new vote($pdo);

$args = [
	'item_id'   => is_numeric($_POST['item_id']) ? $_POST['item_id'] : $_POST['itemId'],
	'item_type' => $_POST['item_type'] ?: $_POST['itemType'],
 'action'    => $_POST['action'],
 'direction' => $_POST['direction'],
	'user_id'   => $_SESSION['user_id'],
];

echo json_encode($vote->vote($args));