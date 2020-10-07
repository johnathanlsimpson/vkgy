<?php

include_once('../php/include.php');

$sql = 'ALTER TABLE `videos` ADD `length` TIME NULL DEFAULT NULL AFTER `youtube_content`';