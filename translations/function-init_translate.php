<?php

include_once('../php/include.php');

if(!$translate) {
	$translate = new translate($pdo);
}