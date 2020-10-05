<?php

include_once('../php/include.php');

$sql = 'ALTER TABLE videos 
ADD youtube_name TEXT NULL DEFAULT NULL AFTER youtube_id,
ADD youtube_content TEXT NULL DEFAULT NULL AFTER youtube_id,
ADD name TEXT NULL DEFAULT NULL AFTER youtube_id,
ADD content TEXT NULL DEFAULT NULL AFTER youtube_id';