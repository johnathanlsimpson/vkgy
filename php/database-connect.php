<?php

// Get $pdo_config and $pdo_opctions from this file
require_once '../php/database-variables.php';

// Create connection to MySQL; if unable to connect first time, try switching host; return false if fails twice (unable to connect to MySQL)
function try_pdo_connection($attempts = 0) {
	global $pdo;
	global $pdo_config;
	global $pdo_options;
	
	if($attempts < 2) {
		try {
			if($attempts > 0) {
				$pdo_config['mysql_host'] = '127.0.0.1';
			}
			
			$pdo_query = 'mysql:host='.$pdo_config['mysql_host'].';charset='.$pdo_config['db_charset'];
			$pdo = new PDO($pdo_query, $pdo_config['mysql_username'], $pdo_config['mysql_password'], $pdo_options);
			
			return true;
		}
		catch(PDOException $e) {
			
			$error_message = date('Y-m-d H:i:s').'attempt #'.$attempts."\n".print_r($_SESSION, true)."\n".print_r($_COOKIE, true)."\n".$e->getMessage()."\n\n---\n\n";
			file_put_contents ("../errors/mysql".str_replace ("/", "|", $_SERVER ["REQUEST_URI"].$_SERVER["PATH_INFO"].$_SERVER["QUERY_STRING"])."-".$_SERVER["REQUEST_METHOD"]."-".$_SERVER["REMOTE_ADDR"], $error_message);
			$attempts++;
			
			if($e->getMessage() === "SQLSTATE[HY000] [2002] Can't connect to local MySQL server through socket '/var/lib/mysql/mysql.sock' (2 \"No such file or directory\")") {
				try_pdo_connection($attempts);
			}
		}
	}
	else {
		return false;
	}
}

// Helper function to separate prepared queries and run them (for creating/populating database, if necessary)
function database_creation_helper($database_query, $pdo) {
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	if($pdo->exec(trim($database_query)) === false) {
		file_put_contents ("../errors/mysql".str_replace ("/", "|", $_SERVER ["REQUEST_URI"].$_SERVER["PATH_INFO"].$_SERVER["QUERY_STRING"])."-".$_SERVER["REQUEST_METHOD"]."-".$_SERVER["REMOTE_ADDR"], $pdo->errorInfo());
	}
}

// If no DB connection set ($pdo), try to connect, then choose appropriate DB
if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
	
	if(strlen($pdo_config['mysql_host']) && strlen($pdo_config['mysql_username']) && strlen($pdo_config['db_name'])) {
		if(try_pdo_connection() && $pdo && $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			
			// If connected to MySQL but DB doesn't exist, create DB and optionally populate it with sample data
			if($pdo->exec('USE '.$pdo_config['db_name']) === false) {
				include('../php/database-create.php');
				database_creation_helper($sql_create_database, $pdo);
				
				if($pdo_config['db_dummy_data']) {
					include('../php/database-data.php');
					database_creation_helper($sql_sample_data, $pdo);
				}
			}
		}
		else {
			// Unable to connect to MySQL
		}
	}
}