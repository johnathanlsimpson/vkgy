<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `images` ADD `image_content` INT NOT NULL DEFAULT "0" AFTER `item_type`;';

$sql[] = '
ALTER TABLE `images` ADD `face_boundaries` TEXT NULL DEFAULT NULL AFTER `hash`;';

$sql[] = '
ALTER TABLE `images` ADD `width` INT NULL AFTER `extension`, ADD `height` INT NULL AFTER `width`;';