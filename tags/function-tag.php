<?php

include_once('../php/include.php');
include_once('../php/class-tag.php');
$tag = new tag($pdo);

// Set up vars
$action    = $_POST['action'] === 'add' ? 'add' : 'remove';
$item_type = in_array($_POST['item_type'], tag::allowed_item_types) ? $_POST['item_type'] : null;
$item_id   = is_numeric($_POST['id']) ? $_POST['id'] : null;
$tag_id    = is_numeric($_POST['tag_id']) ? $_POST['tag_id'] : null;

echo json_encode($tag->update([
	'action' => $action,
	'item_type' => $item_type,
	'item_id' => $item_id,
	'tag_id' => $tag_id,
	'user_id' => $_SESSION['user_id'],
]));