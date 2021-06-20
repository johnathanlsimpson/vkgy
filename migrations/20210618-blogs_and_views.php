<?php

include_once('../php/include.php');

$sql[] = 'RENAME TABLE views_daily_artists TO views_artists_daily;';
$sql[] = 'RENAME TABLE views_weekly_artists TO views_artists_weekly;';
$sql[] = 'RENAME TABLE views_monthly_artists TO views_artists_monthly;';
$sql[] = 'RENAME TABLE views_daily_videos TO views_videos_daily;';
$sql[] = 'RENAME TABLE views_weekly_videos TO views_videos_weekly;';
$sql[] = 'RENAME TABLE views_monthly_videos TO views_videos_monthly;';

$sql[] = "
CREATE TABLE `views_blogs_daily` ( `blog_id` INT NOT NULL , `num_views` INT NOT NULL DEFAULT '1' , PRIMARY KEY (`blog_id`)) ENGINE = InnoDB;";

$sql[] = '
ALTER TABLE `views_blogs_daily` ADD FOREIGN KEY (`blog_id`) REFERENCES `blog`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;';

$sql[] = "
CREATE TABLE IF NOT EXISTS `views_blogs_weekly` (
  `blog_id` int(11) NOT NULL,
  `num_views` int(11) NOT NULL DEFAULT '1',
  `past_views` int(11) NOT NULL DEFAULT '0',
  `past_past_views` int(11) NOT NULL DEFAULT '0',
  `date_occurred` datetime DEFAULT CURRENT_TIMESTAMP
)";

$sql[] = '
ALTER TABLE `views_blogs_weekly` ADD FOREIGN KEY (`blog_id`) REFERENCES `blog`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;';

$sql[] = "
CREATE TABLE IF NOT EXISTS `views_blogs_monthly` (
  `id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `num_views` int(11) NOT NULL DEFAULT '1',
  `date_occurred` datetime DEFAULT CURRENT_TIMESTAMP
)";

$sql[] = '
ALTER TABLE `views_blogs_monthly` ADD FOREIGN KEY (`blog_id`) REFERENCES `blog`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;';