<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `artists_musicians` ADD UNIQUE( `artist_id`, `musician_id`);';

$sql[] = '
ALTER TABLE `artists_musicians` DROP `unique_id`;';

$sql[] = '
ALTER TABLE `artists_musicians` ADD `image_id` INT NULL AFTER `edit_history`;';

$sql[] = '
ALTER TABLE `artists_musicians` ADD FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;';