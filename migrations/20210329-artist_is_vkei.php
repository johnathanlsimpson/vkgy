<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `artists` ADD `is_vkei` BOOLEAN NOT NULL DEFAULT TRUE AFTER `romaji`;';