<?php
	include_once("../php/include.php");
	
	function update_views($view_type, $item_id, $pdo) {
		$allowed_types = ["artist", "blog", "image", "label", "live", "musician", "release", "user", "vip"];
		
		if(in_array($view_type, $allowed_types) && is_numeric($item_id)) {
			$user_id_type = is_numeric($_SESSION["user_id"]) ? "user_id" : "ip_address";
			$user_id = is_numeric($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SERVER["REMOTE_ADDR"];
			
			$sql_check = "SELECT 1 FROM information_schema.tables WHERE table_schema=? AND table_name=?";
			$stmt_check = $pdo->prepare($sql_check);
			$stmt_check->execute([ $pdo_dbname, "views" ]);
			if($stmt_check->fetchColumn()) {
				$sql_add_view = "INSERT INTO views (view_type, item_id, ".$user_id_type.") VALUES (?, ?, ".($user_id_type === "ip_address" ? "INET6_ATON(?)" : "?").")";
				$stmt_add_view = $pdo->prepare($sql_add_view);
				$stmt_add_view->execute([$view_type, $item_id, $user_id]);
			}
		}
	}
?>