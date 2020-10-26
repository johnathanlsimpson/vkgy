<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `lists` ADD `date_occurred` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_private`';