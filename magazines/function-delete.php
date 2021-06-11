<?php

include_once('../php/include.php');
include_once('../php/class-magazine.php');
$access_magazine = new magazine($pdo);

$magazine_id = $_POST['magazine_id'];

$output = $access_magazine->delete_magazine( $magazine_id );

echo json_encode($output);