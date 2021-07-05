<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `songs` CHANGE `variation_of` `variant_of` INT(11) NULL DEFAULT NULL;';

$sql[] = '
ALTER TABLE `songs` DROP `is_custom`;';

$sql[] = '
ALTER TABLE `songs` CHANGE `flat_name` `flat` INT(11) NULL DEFAULT NULL;';

$sql[] = '
ALTER TABLE `songs` CHANGE `differentiation` `hint` INT(11) NULL DEFAULT NULL;';

$sql[] = '
ALTER TABLE `songs` ADD `type` INT NOT NULL DEFAULT "0" AFTER `hint`;';