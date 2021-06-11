<?php

include_once('../php/include.php');
include_once('../php/class-magazine.php');
include_once('../php/class-issue.php');
$access_issue = new issue($pdo);

$issue_id = $_POST['issue_id'];

$output = $access_issue->delete_issue( $issue_id );

echo json_encode($output);