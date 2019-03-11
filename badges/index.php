<?php

include_once('../php/class-access_badge.php');

$access_badge = new access_badge($pdo);
print_r($access_badge->notify_if_new_badge());

//print_r($access_badge->award_badge('additions', 3));
print_r($access_badge->award_badge('patron', 2));
//print_r($access_badge->award_badge('avatar', 3));