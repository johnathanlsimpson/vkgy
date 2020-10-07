<?php

include_once('../php/include.php');

$sql[] = "
CREATE TABLE views_daily_videos (
  video_id int(11) NOT NULL,
  num_views int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

$sql[] = "
ALTER TABLE views_daily_videos
  ADD PRIMARY KEY (video_id);
COMMIT";

$sql[] = "
ALTER TABLE `views_daily_videos` ADD FOREIGN KEY (`video_id`) REFERENCES `videos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";

$sql[] = "
CREATE TABLE views_weekly_videos (
  video_id int(11) NOT NULL,
  num_views int(11) NOT NULL DEFAULT '1',
  past_views int(11) NOT NULL DEFAULT '0',
  past_past_views int(11) NOT NULL DEFAULT '0',
  date_occurred datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

$sql[] = "
ALTER TABLE views_weekly_videos
  ADD PRIMARY KEY (video_id)";

$sql[] = "
ALTER TABLE views_weekly_videos
  ADD CONSTRAINT views_weekly_videos_ibfk_1 FOREIGN KEY (video_id) REFERENCES videos (id) ON DELETE CASCADE ON UPDATE CASCADE";

$sql[] = "
CREATE TABLE views_monthly_videos (
  id int(11) NOT NULL,
  video_id int(11) NOT NULL,
  num_views int(11) NOT NULL DEFAULT '1',
  date_occurred datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

$sql[] = "
ALTER TABLE views_monthly_videos
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY artist_id (video_id,date_occurred)";

$sql[] = "
ALTER TABLE views_monthly_videos
  MODIFY id int(11) NOT NULL AUTO_INCREMENT";

$sql[] = "
ALTER TABLE `views_monthly_videos` ADD FOREIGN KEY (`video_id`) REFERENCES `videos`(`id`) ON DELETE CASCADE ON UPDATE CASCADE";