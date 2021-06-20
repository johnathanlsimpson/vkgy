<?php

include_once('../php/include.php');

/*$old_release_columns = [
	'is_omnibus',
	'is_multi_disc',
	'disc_count',
	'is_verified',
	'user_id', // maybe?
	'is_multi_press',
	'rating',
	'rating_count',
	'is_approved',
];

$old_tracklist_columns = [
	'cover_song_id',
	'song_num',
	'length',
	'credit',
	'credit_romaji',
	'artist_id',
];

$columns_we_need_to_make_int = [
	'venue_limitation',
	'press_limitation_name',
	'press_limitation_num',
];

$columns_we_need_to_split_out = [
	'medium',
	'format',
	'format_name',
	'format_romaji',
	'label_id',
	'publisher_id',
	'marketer_id',
	'distributor_id',
	'manufacturer_id',
	'organizer_id',
];*/

$sql[] = '
CREATE TABLE `songs` ( `id` INT NOT NULL AUTO_INCREMENT , `artist_id` INT NOT NULL , `name` TEXT NOT NULL , `romaji` TEXT NULL , `friendly` TEXT NOT NULL , `flat_name` TEXT NOT NULL , `differentiation` TEXT NULL , `date_occurred` DATE NULL , `variation_of` INT NULL , `cover_of` INT NULL , `notes` TEXT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
';