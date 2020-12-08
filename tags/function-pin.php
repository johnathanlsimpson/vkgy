<?php

include_once('../php/include.php');
include_once('../php/class-tag.php');
$tag = new tag($pdo);

// Set up vars
$action    = sanitize($_POST['action']);
$direction = sanitize($_POST['direction']);
$item_type = sanitize($_POST['item_type']);
$item_id   = is_numeric($_POST['items_tags_id']) ? $_POST['items_tags_id'] : null;

echo json_encode($tag->pin_or_hide([
	'action' => $action,
	'direction' => $direction,
	'item_type' => $item_type,
	'item_id' => $item_id
]));