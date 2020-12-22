<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE `development` ADD UNIQUE(`friendly`);';