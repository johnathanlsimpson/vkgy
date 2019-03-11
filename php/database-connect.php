<?php
	if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
		include_once("../php/database-variables.php");
		
		$pdo_dsn      = "mysql:host=$pdo_host;dbname=$pdo_dbname;charset=$pdo_charset";
		$pdo_options  = array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
			PDO::ATTR_PERSISTENT         => false
		);
		
		try {
			$pdo = new PDO($pdo_dsn, $pdo_username, $pdo_password, $pdo_options);
		}
		catch(PDOException $e) {
			file_put_contents ("../errors/mysql".str_replace ("/", "|", $_SERVER ["REQUEST_URI"].$_SERVER["PATH_INFO"].$_SERVER["QUERY_STRING"])."-".$_SERVER["REQUEST_METHOD"]."-".$_SERVER["REMOTE_ADDR"], $e->getMessage());
			
			if($e->getMessage() === "SQLSTATE[HY000] [2002] Can't connect to local MySQL server through socket '/var/lib/mysql/mysql.sock' (2 \"No such file or directory\")") {
				$pdo_host = "127.0.0.1";
				try {
					$pdo = new PDO($pdo_dsn, $pdo_username, $pdo_password, $pdo_options);
				}
				catch(PDOException $e) {
					file_put_contents ("../errors/mysql".str_replace ("/", "|", $_SERVER ["REQUEST_URI"].$_SERVER["PATH_INFO"].$_SERVER["QUERY_STRING"])."-".$_SERVER["REQUEST_METHOD"]."-".$_SERVER["REMOTE_ADDR"], $e->getMessage());
				}
			}
		}
	}
?>