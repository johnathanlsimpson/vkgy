<?php

include_once('../php/include.php');
include_once('../php/class-magazine.php');
$access_magazine = new magazine($pdo);

$attribute_id = $_POST['attribute_id'];

$output = $access_magazine->delete_attribute( $attribute_id );

echo json_encode($output);