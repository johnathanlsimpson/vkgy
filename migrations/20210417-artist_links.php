<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `artists_urls` ADD `is_active` BOOLEAN NOT NULL DEFAULT TRUE AFTER `is_retired`;';

$sql[] = '
UPDATE artists_urls SET is_active=1 WHERE is_retired=0;';

$sql[] = '
UPDATE artists_urls SET is_active=0 WHERE is_retired=1;';