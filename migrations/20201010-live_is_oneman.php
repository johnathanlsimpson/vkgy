<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `lives_artists` ADD `is_sponsor` BOOLEAN NOT NULL DEFAULT FALSE AFTER `artist_id`';

$sql[] = '
UPDATE lives SET type=0 WHERE type="sponsored event" OR type="other" OR type IS NULL';
$sql[] = '
UPDATE lives SET type=1 WHERE type="oneman"';
$sql[] = '
UPDATE lives SET type=2 WHERE type="twoman"';
$sql[] = '
UPDATE lives SET type=3 WHERE type="threeman"';
$sql[] = '
UPDATE lives SET type=4 WHERE type="fourman"';
$sql[] = '
UPDATE lives SET type=5 WHERE type="session event"';

$sql[] = '
ALTER TABLE `lives` CHANGE `type` `type` TINYINT NOT NULL DEFAULT "0"';