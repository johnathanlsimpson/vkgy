<?php

include_once('../php/class-access_list.php');

$access_list = new access_list($pdo);

if( is_numeric($_GET['id']) ) {
	
	$list_id = $_GET['id'];
	
	$list = $access_list->access_list([ 'id' => $list_id, 'get' => 'all' ]);
	
	include('../lists/page-list.php');
	
}