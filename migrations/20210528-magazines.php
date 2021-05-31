<?php

include_once('../php/include.php');

$sql[] = '
RENAME TABLE magazines TO magazines_series;';

$sql[] = '
CREATE TABLE `magazines_attributes` ( `id` INT NOT NULL AUTO_INCREMENT , `type` INT NOT NULL DEFAULT "0" COMMENT "format / size / pages" , `name` TEXT NOT NULL , `romaji` TEXT NULL , `friendly` TEXT NOT NULL , `is_default` BOOLEAN NOT NULL DEFAULT FALSE , PRIMARY KEY (`id`)) ENGINE = InnoDB;';

$sql[] = '
CREATE TABLE `magazines_series_labels` ( `id` INT NOT NULL AUTO_INCREMENT , `magazine_series_id` INT NOT NULL , `label_id` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';

$sql[] = '
ALTER TABLE `magazines_series_labels` ADD INDEX(`magazine_series_id`);';

$sql[] = '
ALTER TABLE `magazines_series_labels` ADD INDEX(`label_id`);';

$sql[] = '
CREATE TABLE `images_issues` ( `id` INT NOT NULL AUTO_INCREMENT , `image_id` INT NOT NULL , `issue_id` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`issue_id`)) ENGINE = InnoDB;';

$sql[] = '
ALTER TABLE `magazines_series` ADD `parent_magazine_series_id` INT NULL AFTER `friendly`, ADD `volume_pattern` TEXT NOT NULL AFTER `parent_magazine_series_id`, ADD `default_price` INT NULL AFTER `volume_pattern`, ADD `notes` TEXT NULL AFTER `default_price`;';

$sql[] = '
CREATE TABLE `magazines` ( `id` INT NULL , `magazine_series_id` INT NOT NULL , `date_represented` DATETIME NOT NULL , `date_occurred` DATETIME NULL , `volume` TEXT NOT NULL , `product_number` INT NULL , `jan_code` INT NULL , `notes` INT NOT NULL , PRIMARY KEY (`id`), INDEX (`magazine_series_id`)) ENGINE = InnoDB;';

$sql[] = '
CREATE TABLE `magazines_artists` ( `id` INT NOT NULL , `magazine_id` INT NOT NULL , `artist_id` INT NOT NULL , `is_cover` BOOLEAN NOT NULL DEFAULT FALSE , `is_large` BOOLEAN NOT NULL DEFAULT FALSE , `is_normal` BOOLEAN NOT NULL DEFAULT FALSE , `is_flyer` BOOLEAN NOT NULL DEFAULT FALSE , PRIMARY KEY (`id`), INDEX (`magazine_id`), INDEX (`artist_id`)) ENGINE = InnoDB;';

$sql[] = '
ALTER TABLE `magazines` ADD `image_id` INT NULL AFTER `volume`, ADD `friendly` INT NOT NULL AFTER `image_id`;';

$sql[] = '
ALTER TABLE `magazines` CHANGE `volume` `volume_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL;';

$sql[] = '
ALTER TABLE `magazines` ADD `volume_romaji` TEXT NULL AFTER `volume_name`;';

$sql[] = '
ALTER TABLE `magazines_series` DROP `volume_pattern`;';

$sql[] = '
ALTER TABLE `magazines_series` ADD `volume_name_pattern` TEXT NOT NULL AFTER `friendly`, ADD `volume_romaji_pattern` TEXT NOT NULL AFTER `volume_name_pattern`, ADD `num_volume_digits` INT NOT NULL DEFAULT "2" AFTER `volume_romaji_pattern`;';

$sql[] = '
ALTER TABLE `magazines_series` CHANGE `default_price` `default_price` TEXT NULL DEFAULT NULL;';

$sql[] = '
ALTER TABLE `magazines_series` CHANGE `volume_romaji_pattern` `volume_romaji_pattern` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT NULL;';

$sql[] = '
ALTER TABLE `magazines` ADD `volume_is_custom` BOOLEAN NOT NULL DEFAULT FALSE AFTER `volume_romaji`;';

$sql[] = '
ALTER TABLE `magazines` CHANGE `friendly` `friendly` TEXT NOT NULL, CHANGE `product_number` `product_number` TEXT NULL DEFAULT NULL, CHANGE `jan_code` `jan_code` TEXT NULL DEFAULT NULL, CHANGE `notes` `notes` TEXT NULL;';

$sql[] = '
ALTER TABLE `magazines` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;';

$sql[] = '
ALTER TABLE `magazines` ADD FOREIGN KEY (`magazine_series_id`) REFERENCES `magazines_series`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;';

$sql[] = '
ALTER TABLE `magazines` CHANGE `date_represented` `date_represented` DATETIME NULL;';

// Bro I don't know how I messed up the creation of these tables so badly
$sql[] = '
ALTER TABLE `magazines_artists` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;';

$sql[] = '
ALTER TABLE `magazines_artists` ADD UNIQUE( `magazine_id`, `artist_id`);';

$sql[] = '
TABLE `magazines` ADD INDEX(`magazine_series_id`);';

// The names just were just getting complicated
$sql[] = '
RENAME TABLE magazines TO issues;';

$sql[] = '
RENAME TABLE magazines_artists TO issues_artists;';

$sql[] = '
RENAME TABLE magazines_series_labels TO magazines_labels;';

$sql[] = '
RENAME TABLE magazines_series TO magazines;';

$sql[] = '
ALTER TABLE `magazines` CHANGE `parent_magazine_series_id` `parent_magazine_id` INT(11) NULL DEFAULT NULL;';

$sql[] = '
ALTER TABLE `magazines_labels` CHANGE `magazine_series_id` `magazine_id` INT(11) NOT NULL;';

$sql[] = '
ALTER TABLE `issues` CHANGE `magazine_series_id` `magazine_id` INT(11) NOT NULL;';

$sql[] = '
ALTER TABLE `issues_artists` CHANGE `magazine_id` `issue_id` INT(11) NOT NULL;';

$sql[] = '
ALTER TABLE `magazines_labels` ADD UNIQUE( `magazine_id`, `label_id`);';

$sql[] = '
ALTER TABLE `issues` ADD `price` TEXT NULL AFTER `friendly`;';

$sql[] = '
ALTER TABLE `images_issues` ADD INDEX(`image_id`);';

$sql[] = '
ALTER TABLE `images_issues` ADD FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;';

$sql[] = '
ALTER TABLE `images_issues` ADD FOREIGN KEY (`issue_id`) REFERENCES `issues`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;';