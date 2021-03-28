<?php

include_once('../php/include.php');

$sql[] = '
CREATE TABLE `artists_years` ( `id` INT NOT NULL AUTO_INCREMENT , `artist_id` INT NOT NULL , `year` YEAR NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';

$sql[] = '
ALTER TABLE `artists_years` ADD INDEX(`artist_id`);';

$sql[] = '
ALTER TABLE `artists_years` ADD FOREIGN KEY (`artist_id`) REFERENCES `artists`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;';

$sql[] = '
ALTER TABLE `artists_years` ADD INDEX(`year`);';

$sql[] = '
ALTER TABLE `artists_years` ADD UNIQUE( `artist_id`, `year`);';