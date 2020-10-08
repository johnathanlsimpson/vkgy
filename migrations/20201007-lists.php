<?php

include_once('../php/include.php');

$sql[] = 'CREATE TABLE IF NOT EXISTS lists (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  `name` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  friendly text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_private tinyint(1) NOT NULL DEFAULT "0"
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';

$sql[] = 'ALTER TABLE lists
  ADD PRIMARY KEY (id),
  ADD KEY user_id (user_id)';

$sql[] = 'ALTER TABLE lists
  MODIFY id int(11) NOT NULL AUTO_INCREMENT';

$sql[] = 'CREATE TABLE IF NOT EXISTS lists_items (
  id int(11) NOT NULL,
  list_id int(11) NOT NULL,
  item_id int(11) NOT NULL,
  item_type int(11) NOT NULL,
  date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';

$sql[] = 'ALTER TABLE lists_items
  ADD PRIMARY KEY (id),
  ADD KEY list_id (list_id),
  ADD KEY item_id (item_id)';

$sql[] = 'ALTER TABLE lists_items
  MODIFY id int(11) NOT NULL AUTO_INCREMENT';