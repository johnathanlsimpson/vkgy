<?php

include_once('../php/include.php');

$sql = 'ALTER TABLE videos 
ADD type TINYINT NOT NULL DEFAULT "0" AFTER youtube_id,
ADD content TEXT NULL DEFAULT NULL AFTER youtube_id,
ADD name TEXT NULL DEFAULT NULL AFTER youtube_id,
ADD youtube_content TEXT NULL DEFAULT NULL AFTER youtube_id,
ADD youtube_name TEXT NULL DEFAULT NULL AFTER youtube_id';