<?php

// Set database connection variables here; used in /php/database-connect.php
$pdo_config = [
	'mysql_host'     => 'localhost',
	'mysql_username' => '',
	'mysql_password' => '',
	'db_name'        => '',
	'db_charset'     => 'utf8mb4',
	'db_collation'   => 'utf8mb4_unicode_520_ci',
	'db_dummy_data'  => true, // populates database with dummy data
];

// Default connection options
$pdo_options = [
	PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	PDO::ATTR_EMULATE_PREPARES   => false,
	PDO::ATTR_PERSISTENT         => false
];