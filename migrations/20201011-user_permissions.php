<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `users` ADD `permissions` TEXT NULL AFTER `can_edit_permissions`, ADD `preferences` TEXT NULL AFTER `permissions`';