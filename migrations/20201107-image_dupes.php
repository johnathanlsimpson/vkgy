<?php

include_once('../php/include.php');

$sql[] = '
ALTER TABLE images ADD hash VARCHAR(40) DEFAULT NULL AFTER credit';