<?php

// Database structure
// Updated 2019-04-04
$sql_create_database = '
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `'.$pdo_config['db_name'].'` DEFAULT CHARACTER SET '.$pdo_config['db_charset'].' COLLATE '.$pdo_config['db_collation'].';
USE `'.$pdo_config['db_name'].'`;

'."




/* ===========================================
   Step 0. README
    
	 Updating DB:
	 1. Make changes in phpadmin.
	 2. Update this file (see Step 1).
	 3. Manually delete your DB via phpadmin
	    (Operations > Drop Database).
   4. Update php/database-variables.php and
	    turn on dummy data creation.
	 5. Navigate to / in your browser, and the
	    DB should be reconstructed.
   =========================================== */




/* ===========================================
   Step 1. CREATE TABLE
	 
	 Updating this file:
	 1. Make your DB changes via phpmyadmin.
	 2. For each table that has been changed:
	    Export > Custom > Output as Text
      > Structure Only > in Add Statements,
      select only 'Add Create Table' and
      'Create Table If Not Exists'
	 3. From the resulting SQL, copy the 'CREATE
	    TABLE IF NOT EXISTS' query.
	 4. Paste it into the section below (copying
	    over the previous query for the table,
      if the table already existed).
   5. !!!! Replace each COLLATE string with:
	    ".$pdo_config['db_collation']."
      Usually it's utf8mb4_unicode_520_ci
	 6. !!!! Do the same for each CHARSET
	    string:
	    ".$pdo_config['db_charset']."
	    See below for examples.
   7. Repeat 3~6 for INDEXES/KEY query
	    (search for Step 2 in this document),
	    AUTO_INCREMENT query (search Step 3),
	    and CONSTRAINTS (search Step 4).
   =========================================== */
	 
CREATE TABLE IF NOT EXISTS `areas_artists` (
  `id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `artists` (
  `id` int(11) NOT NULL,
  `name` mediumtext COLLATE ".$pdo_config['db_collation']." NOT NULL,
  `romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
  `is_exclusive` int(11) NOT NULL DEFAULT '0',
  `type` int(1) DEFAULT '1',
  `active` tinyint(2) NOT NULL DEFAULT '0',
  `friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL,
  `concept_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
  `concept_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
  `description` mediumtext COLLATE ".$pdo_config['db_collation'].",
  `label_history` mediumtext COLLATE ".$pdo_config['db_collation'].",
  `official_links` mediumtext COLLATE ".$pdo_config['db_collation'].",
  `date_occurred` date DEFAULT NULL,
  `date_ended` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `artists_bio` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL DEFAULT '0',
	`date_occurred` date DEFAULT NULL,
	`type` mediumtext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`content` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`user_id` int(11) DEFAULT '0',
	`date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `artists_musicians` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL DEFAULT '0',
	`musician_id` int(11) NOT NULL DEFAULT '0',
	`as_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`as_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`position` int(11) NOT NULL DEFAULT '0',
	`position_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`position_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`from_start` int(11) NOT NULL DEFAULT '1',
	`to_end` int(11) NOT NULL DEFAULT '1',
	`dates_active` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`edit_history` text COLLATE ".$pdo_config['db_collation'].",
	`unique_id` varchar(21) COLLATE ".$pdo_config['db_collation']." NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `artists_tags` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL,
	`tag_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_occurred` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `artists_views` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL,
	`user_id` int(11) DEFAULT NULL,
	`date_occurred` date DEFAULT NULL,
	`view_count` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `artists_websites` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL DEFAULT '0',
	`url` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `badges` (
	`id` int(11) NOT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`friendly` varchar(20) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`name` text COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`description` text COLLATE ".$pdo_config['db_collation'].",
	`description_level_1` text COLLATE ".$pdo_config['db_collation'].",
	`description_level_2` text COLLATE ".$pdo_config['db_collation'].",
	`description_level_3` text COLLATE ".$pdo_config['db_collation'].",
	`is_secret` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `blog` (
	`id` int(11) NOT NULL,
	`title` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`content` longtext COLLATE ".$pdo_config['db_collation'].",
	`date_occurred` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` bigint(5) DEFAULT '0',
	`image_id` int(11) DEFAULT NULL,
	`image` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`is_locked` tinyint(1) DEFAULT NULL,
	`tags` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`tags_artists` varchar(150) COLLATE ".$pdo_config['db_collation']." DEFAULT NULL,
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." DEFAULT NULL,
	`is_archived` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `blog_artists` (
	`id` int(11) NOT NULL,
	`blog_id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_occurred` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `blog_tags` (
	`id` int(11) NOT NULL,
	`blog_id` int(11) NOT NULL,
	`tag_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_occurred` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `comments` (
	`id` int(11) NOT NULL,
	`date_occurred` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`edit_history` text COLLATE ".$pdo_config['db_collation'].",
	`user_id` int(11) DEFAULT NULL,
	`anonymous_id` text COLLATE ".$pdo_config['db_collation'].",
	`thread_id` int(11) DEFAULT NULL,
	`content` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`item_id` int(11) NOT NULL DEFAULT '0',
	`item_type` int(11) NOT NULL DEFAULT '0',
	`is_deleted` int(1) NOT NULL DEFAULT '0',
	`is_approved` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `comments_likes` (
	`id` int(11) NOT NULL,
	`comment_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`anonymous_id` text COLLATE ".$pdo_config['db_collation'].",
	`date_occurred` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `edits_artists` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `edits_blog` (
	`id` int(11) NOT NULL,
	`blog_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `edits_labels` (
	`id` int(11) NOT NULL,
	`label_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `edits_musicians` (
	`id` int(11) NOT NULL,
	`musician_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `edits_releases` (
	`id` int(11) NOT NULL,
	`release_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `feed` (
	`id` int(11) NOT NULL,
	`title` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`type` mediumtext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`artist_id` int(11) DEFAULT '0',
	`description` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`image` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`url` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`linktitle` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`tweet` mediumtext COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `images` (
	`id` int(11) NOT NULL,
	`extension` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	`is_default` tinyint(1) DEFAULT NULL,
	`is_exclusive` tinyint(1) DEFAULT NULL,
	`is_release` tinyint(1) DEFAULT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`artist_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`release_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`musician_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
	`friendly` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
	`credit` text CHARACTER SET utf8 COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `labels` (
	`id` int(11) NOT NULL,
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`type` int(11) NOT NULL DEFAULT '0',
	`president_id` int(11) DEFAULT NULL,
	`president_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`president_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_started` date DEFAULT NULL,
	`date_ended` date DEFAULT NULL,
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." DEFAULT NULL,
	`history` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`parent_label_id` int(11) DEFAULT NULL,
	`description` text COLLATE ".$pdo_config['db_collation'].",
	`official_links` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `labels_bio` (
	`id` int(11) NOT NULL,
	`label_id` int(11) NOT NULL DEFAULT '0',
	`date_occurred` date DEFAULT NULL,
	`content` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`user_id` int(11) DEFAULT '0',
	`date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `lives` (
	`id` int(11) NOT NULL,
	`date_occurred` date NOT NULL,
	`livehouse_id` int(11) NOT NULL,
	`lineup` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`type` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`tour_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`tour_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`sup_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`sup_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`sub_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`sub_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`presenter_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`sponsor_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`notes` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`setlist` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`friendly` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `areas` (
	`id` int(11) NOT NULL,
	`name` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`romaji` text COLLATE ".$pdo_config['db_collation'].",
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`parent_id` int(11) DEFAULT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(11) NOT NULL DEFAULT '1',
	`prefecture_name` text COLLATE ".$pdo_config['db_collation'].",
	`prefecture_romaji` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `lives_artists` (
	`id` int(11) NOT NULL,
	`live_id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `lives_livehouses` (
	`id` int(11) NOT NULL,
	`name` mediumtext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`renamed_to` int(11) DEFAULT NULL,
	`parent_id` int(11) DEFAULT NULL,
	`capacity` int(11) DEFAULT NULL,
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." DEFAULT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`area_id` int(11) NOT NULL DEFAULT '0',
	`history` text COLLATE ".$pdo_config['db_collation'].",
	`is_duplicate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `lives_nicknames` (
	`id` int(11) NOT NULL,
	`livehouse_id` int(11) NOT NULL,
	`nickname` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `messages` (
	`id` int(11) NOT NULL,
	`fromuser` int(11) DEFAULT '1',
	`touser` int(11) DEFAULT NULL,
	`replyto` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`subject` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`message` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`unread` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `musicians` (
	`id` int(11) NOT NULL,
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`blood_type` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`birth_date` date DEFAULT NULL,
	`gender` int(11) DEFAULT '1',
	`birthplace` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`aliases` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`aliases_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`usual_position` int(11) NOT NULL DEFAULT '0' COMMENT '0: other / 1: vocal / 2: guitar / 3: bass / 4: drum',
	`friendly` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`history` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`edit_history` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `queued_aod` (
	`id` int(11) NOT NULL,
	`artist_id` int(11) NOT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `queued_flyers` (
	`id` int(11) NOT NULL,
	`extension` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	`is_default` tinyint(1) DEFAULT NULL,
	`is_exclusive` tinyint(1) DEFAULT NULL,
	`is_release` tinyint(1) DEFAULT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`artist_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`release_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`musician_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	`description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
	`friendly` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
	`credit` text CHARACTER SET utf8 COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `queued_fod` (
	`id` int(11) NOT NULL,
	`image_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `queued_social` (
	`id` int(11) NOT NULL,
	`social_type` text COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`item_type` text COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`item_id` int(11) NOT NULL,
	`content` text COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`image` text COLLATE ".$pdo_config['db_collation'].",
	`url` text COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`is_completed` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases` (
	`id` int(11) NOT NULL,
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`friendly` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`artist_id` int(11) NOT NULL DEFAULT '0',
	`date_occurred` date DEFAULT NULL,
	`is_omnibus` int(11) DEFAULT '0',
	`type_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`type_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`is_multi_disc` int(11) DEFAULT '0',
	`disc_count` int(11) DEFAULT '0',
	`price` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`upc` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`medium` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`format` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`format_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`format_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`venue_limitation` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`press_limitation_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`press_limitation_num` text COLLATE ".$pdo_config['db_collation'].",
	`label_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`publisher_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`marketer_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`distributor_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`manufacturer_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`organizer_id` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`concept` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`concept_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`is_verified` int(11) DEFAULT '0',
	`cover_id` int(11) DEFAULT NULL,
	`notes` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`credits` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`user_id` int(11) DEFAULT NULL,
	`is_multi_press` int(11) DEFAULT '0',
	`press_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`press_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`rating` int(11) DEFAULT '0',
	`rating_count` int(11) DEFAULT '0',
	`is_approved` int(11) NOT NULL DEFAULT '0' COMMENT 'approved by moderator?',
	`is_sold_out` int(11) NOT NULL DEFAULT '0' COMMENT 'Stock status of release',
	`case_type` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`jan_code` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`artist_display_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`artist_display_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`cover` text COLLATE ".$pdo_config['db_collation']."
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases_collections` (
	`id` int(11) NOT NULL,
	`user_id` int(5) DEFAULT NULL,
	`release_id` int(5) DEFAULT NULL,
	`is_for_sale` int(11) NOT NULL DEFAULT '0',
	`date_occurred` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases_likes` (
	`id` int(11) NOT NULL,
	`comment_id` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) DEFAULT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ip_address` varbinary(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases_ratings` (
	`id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL DEFAULT '0',
	`ip_address` varbinary(16) DEFAULT NULL,
	`release_id` int(11) NOT NULL DEFAULT '0',
	`rating` int(11) NOT NULL DEFAULT '1',
	`date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation']." ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS `releases_tags` (
	`id` int(11) NOT NULL,
	`release_id` int(11) NOT NULL,
	`tag_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_occurred` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases_tracklists` (
	`id` int(11) NOT NULL,
	`release_id` int(11) DEFAULT NULL,
	`disc_num` int(11) DEFAULT NULL,
	`disc_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`disc_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`section_num` int(11) DEFAULT NULL,
	`section_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`section_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`track_num` int(11) DEFAULT NULL,
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`artist_id` int(11) DEFAULT NULL,
	`artist_display_name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`artist_display_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`song_id` int(11) DEFAULT NULL,
	`cover_song_id` int(11) DEFAULT NULL,
	`song_num` int(11) DEFAULT NULL,
	`length` int(11) DEFAULT NULL,
	`credit` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`credit_romaji` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases_views` (
	`id` int(11) NOT NULL,
	`releaseid` int(11) NOT NULL,
	`userid` int(11) DEFAULT NULL,
	`dateviewed` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `releases_wants` (
	`id` int(11) NOT NULL,
	`user_id` int(5) DEFAULT NULL,
	`release_id` int(5) DEFAULT NULL,
	`date_occurred` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `tags` (
	`id` int(11) NOT NULL,
	`tag` mediumtext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`count` bigint(10) DEFAULT '0',
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `tags_artists` (
	`id` int(11) NOT NULL,
	`name` tinytext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`romaji` tinytext COLLATE ".$pdo_config['db_collation'].",
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`is_admin_tag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `tags_releases` (
	`id` int(11) NOT NULL,
	`name` tinytext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`romaji` tinytext COLLATE ".$pdo_config['db_collation'].",
	`friendly` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`is_admin_tag` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `users` (
	`id` int(11) NOT NULL,
	`username` varchar(100) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`password_old` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`password` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`email` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`rank` tinyint(2) DEFAULT NULL,
	`is_vip` int(1) NOT NULL DEFAULT '0',
	`vip_since` date DEFAULT NULL,
	`name` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`motto` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`website` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`hash` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`twitter` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`tumblr` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`facebook` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`lastfm` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`icon` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`birthday` date DEFAULT NULL,
	`gender` tinyint(2) DEFAULT '0',
	`date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`artist_id` int(11) DEFAULT NULL,
	`ip_address` varchar(50) COLLATE ".$pdo_config['db_collation']." DEFAULT NULL,
	`tag_hash` text COLLATE ".$pdo_config['db_collation'].",
	`fan_since` int(11) DEFAULT NULL,
	`site_theme` int(11) NOT NULL DEFAULT '0',
	`site_lang` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `users_avatars` (
	`id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`content` text COLLATE ".$pdo_config['db_collation'].",
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `users_badges` (
	`id` int(11) NOT NULL,
	`badge_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`unique_id` varchar(20) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`level` int(1) NOT NULL DEFAULT '0',
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`is_unseen` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `users_spam` (
	`id` int(11) NOT NULL,
	`username` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`password` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`email` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`ip_address` varchar(50) COLLATE ".$pdo_config['db_collation']." DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `users_tokens` (
	`id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`token` text COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`remote_addr` text COLLATE ".$pdo_config['db_collation']." NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `users_views` (
	`id` int(11) NOT NULL,
	`to_user` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `views` (
	`id` int(11) NOT NULL,
	`view_type` varchar(10) COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`item_id` int(11) NOT NULL,
	`user_id` int(11) DEFAULT NULL,
	`ip_address` varbinary(16) DEFAULT NULL,
	`date_occurred` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `vip` (
	`id` int(11) NOT NULL,
	`title` mediumtext COLLATE ".$pdo_config['db_collation']." NOT NULL,
	`content` longtext COLLATE ".$pdo_config['db_collation'].",
	`artists` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`categories` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`releases` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`user_id` int(11) DEFAULT NULL,
	`date_occurred` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`bump_date` timestamp NULL DEFAULT NULL,
	`friendly` varchar(150) COLLATE ".$pdo_config['db_collation']." NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `vip_comments` (
	`id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`post_id` int(11) NOT NULL,
	`comment` mediumtext COLLATE ".$pdo_config['db_collation'].",
	`edit_history` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `vip_thanks` (
	`id` int(11) NOT NULL,
	`post_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";

CREATE TABLE IF NOT EXISTS `vip_views` (
	`id` int(11) NOT NULL,
	`post_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`comment_id` int(11) DEFAULT NULL,
	`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=".$pdo_config['db_charset']." COLLATE=".$pdo_config['db_collation'].";




/* ===========================================
   Step 2. INDEXES/KEY
   =========================================== */
ALTER TABLE `areas_artists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `artist_id` (`artist_id`),
  ADD KEY `area_id` (`area_id`);

ALTER TABLE `artists`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `artists_bio`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `artists_musicians`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `unique_id` (`unique_id`),
	ADD KEY `musician_id` (`musician_id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `artists_tags`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`),
	ADD KEY `tag_id` (`tag_id`);

ALTER TABLE `artists_views`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `artist_id` (`artist_id`,`date_occurred`),
	ADD KEY `user_id` (`user_id`);

ALTER TABLE `artists_websites`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `badges`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `blog`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`),
	ADD KEY `fromuser` (`user_id`),
	ADD KEY `tags_artists` (`tags_artists`),
	ADD KEY `date_occurred` (`date_occurred`),
	ADD FULLTEXT KEY `postsearch` (`title`);

ALTER TABLE `blog_artists`
	ADD PRIMARY KEY (`id`),
	ADD KEY `blog_id` (`blog_id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `blog_tags`
	ADD PRIMARY KEY (`id`),
	ADD KEY `blog_id` (`blog_id`),
	ADD KEY `tag_id` (`tag_id`);

ALTER TABLE `comments`
	ADD PRIMARY KEY (`id`),
	ADD KEY `entryid` (`item_id`),
	ADD KEY `is_deleted` (`is_deleted`),
	ADD KEY `is_approved` (`is_approved`);

ALTER TABLE `comments_likes`
	ADD PRIMARY KEY (`id`),
	ADD KEY `comment_id` (`comment_id`),
	ADD KEY `user_id` (`user_id`);

ALTER TABLE `edits_artists`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `edits_blog`
	ADD PRIMARY KEY (`id`),
	ADD KEY `release_id` (`blog_id`);

ALTER TABLE `edits_labels`
	ADD PRIMARY KEY (`id`),
	ADD KEY `release_id` (`label_id`);

ALTER TABLE `edits_musicians`
	ADD PRIMARY KEY (`id`),
	ADD KEY `musician_id` (`musician_id`);

ALTER TABLE `edits_releases`
	ADD PRIMARY KEY (`id`),
	ADD KEY `release_id` (`release_id`);

ALTER TABLE `feed`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `images`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `labels`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `labels_bio`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`label_id`);

ALTER TABLE `lives`
	ADD PRIMARY KEY (`id`),
	ADD KEY `date_occurred` (`date_occurred`),
	ADD KEY `livehouse_id` (`livehouse_id`);

ALTER TABLE `areas`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `name` (`name`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `lives_artists`
	ADD PRIMARY KEY (`id`),
	ADD KEY `live_id` (`live_id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `lives_livehouses`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `lives_nicknames`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `nickname` (`nickname`);

ALTER TABLE `messages`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `musicians`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `queued_aod`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `queued_flyers`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`);

ALTER TABLE `queued_fod`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `queued_social`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `releases`
	ADD PRIMARY KEY (`id`),
	ADD KEY `artist_id` (`artist_id`),
	ADD KEY `date_occurred` (`date_occurred`);

ALTER TABLE `releases_collections`
	ADD PRIMARY KEY (`id`),
	ADD KEY `user_id` (`user_id`),
	ADD KEY `release_id` (`release_id`);

ALTER TABLE `releases_likes`
	ADD PRIMARY KEY (`id`),
	ADD KEY `user_id` (`user_id`);

ALTER TABLE `releases_ratings`
	ADD PRIMARY KEY (`id`),
	ADD KEY `user_id` (`user_id`);

ALTER TABLE `releases_tags`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `releases_tracklists`
	ADD PRIMARY KEY (`id`),
	ADD KEY `release_id` (`release_id`);

ALTER TABLE `releases_views`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `releases_wants`
	ADD PRIMARY KEY (`id`),
	ADD KEY `user_id` (`user_id`);

ALTER TABLE `tags`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `tags_artists`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `tags_releases`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `users`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `users_avatars`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `user_id` (`user_id`);

ALTER TABLE `users_badges`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `unique_id` (`unique_id`),
	ADD KEY `badge_id` (`badge_id`),
	ADD KEY `user_id` (`user_id`),
	ADD KEY `user_id_2` (`user_id`,`is_unseen`);

ALTER TABLE `users_spam`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `users_tokens`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `users_views`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `views`
	ADD PRIMARY KEY (`id`),
	ADD KEY `view_type` (`view_type`),
	ADD KEY `item_id` (`item_id`),
	ADD KEY `user_id` (`user_id`);

ALTER TABLE `vip`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `friendly` (`friendly`);

ALTER TABLE `vip_comments`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `vip_thanks`
	ADD PRIMARY KEY (`id`);

ALTER TABLE `vip_views`
	ADD PRIMARY KEY (`id`);




/* ===========================================
   Step 3. AUTO_INCREMENT
   =========================================== */

ALTER TABLE `areas_artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `artists`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `artists_bio`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `artists_musicians`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `artists_tags`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `artists_views`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `artists_websites`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `badges`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `blog`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `blog_artists`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `blog_tags`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `comments`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `comments_likes`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edits_artists`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edits_blog`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edits_labels`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edits_musicians`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `edits_releases`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `feed`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `images`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `labels`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `labels_bio`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `lives`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `areas`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `lives_artists`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `lives_livehouses`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `lives_nicknames`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `messages`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `musicians`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `queued_aod`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `queued_flyers`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `queued_fod`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `queued_social`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_collections`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_likes`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_ratings`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_tags`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_tracklists`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_views`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `releases_wants`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tags`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tags_artists`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tags_releases`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_avatars`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_badges`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_spam`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_tokens`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_views`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `views`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `vip`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `vip_comments`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `vip_thanks`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `vip_views`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;




/* ===========================================
   Step 4. CONSTRAINTS
   =========================================== */

ALTER TABLE `areas_artists`
  ADD CONSTRAINT `areas_artists_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `areas_artists_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `artists_bio`
	ADD CONSTRAINT `artists_bio_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `artists_musicians`
	ADD CONSTRAINT `artists_musicians_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
	ADD CONSTRAINT `artists_musicians_ibfk_2` FOREIGN KEY (`musician_id`) REFERENCES `musicians` (`id`) ON DELETE CASCADE;

ALTER TABLE `artists_views`
	ADD CONSTRAINT `artists_views_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `artists_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `artists_websites`
	ADD CONSTRAINT `artists_websites_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `blog_artists`
	ADD CONSTRAINT `blog_artists_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `blog_artists_ibfk_2` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `blog_tags`
	ADD CONSTRAINT `blog_tags_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `blog_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `edits_artists`
	ADD CONSTRAINT `edits_artists_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `edits_blog`
	ADD CONSTRAINT `edits_blog_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `edits_labels`
	ADD CONSTRAINT `edits_labels_ibfk_1` FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `edits_musicians`
	ADD CONSTRAINT `edits_musicians_ibfk_1` FOREIGN KEY (`musician_id`) REFERENCES `musicians` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `edits_releases`
	ADD CONSTRAINT `edits_releases_ibfk_1` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `releases_collections`
	ADD CONSTRAINT `releases_collections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `releases_collections_ibfk_2` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `releases_likes`
	ADD CONSTRAINT `releases_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `releases_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `releases_ratings`
	ADD CONSTRAINT `releases_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `releases_tracklists`
	ADD CONSTRAINT `releases_tracklists_ibfk_1` FOREIGN KEY (`release_id`) REFERENCES `releases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `releases_wants`
	ADD CONSTRAINT `releases_wants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users_badges`
	ADD CONSTRAINT `users_badges_ibfk_1` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `users_badges_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
";