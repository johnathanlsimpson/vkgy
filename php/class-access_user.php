<?php
	include_once("../php/include.php");
	
	class access_user {
		public  $pdo;
		private $user_list = ["by_id" => [], "by_username" => []];
		
		
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once("../php/database-connect.php");
			}
			$this->pdo = $pdo;
			
			$this->allowed_icons = [
				'crown',
				'heart',
				'star',
				'flower',
			];
		}
		
		
		
		// ======================================================
		// User data object
		// ======================================================
		function access_user($args = []) {
			// USERNAME TO ID
			if($args["username"] && in_array($args["username"], array_keys($this->user_list["by_username"]))) {
				$args["id"] = $this->user_list["by_username"][sanitize($args["username"])]["id"];
				unset($args["username"]);
			}
			
			// SELECT
			switch($args["get"]) {
				case "name" :
					$sql_select = ['id', 'username', 'rank', 'is_vip', 'icon'];
					break;
				case "all" :
					$sql_select = [
						"users.id",
						"users.username",
						"users.rank AS is_editor",
						"users.is_vip",
						"users.name",
						"users.email",
						"users.motto",
						"users.website",
						"users.twitter",
						"users.tumblr",
						"users.facebook",
						"users.lastfm",
						'users.mh',
						"users.icon",
						"users.birthday",
						"users.pronouns",
						"users.date_added",
						"users.artist_id",
						'users.fan_since',
						'users.site_theme'
					];
					break;
				case "list":
					$sql_select = [
						"users.id",
						"users.is_vip",
						"users.rank AS is_editor",
						"users.date_added",
						"users.username",
					];
			}
			
			// FROM
			$sql_from = is_array($sql_from) ? $sql_from : ["users"];
			
			// WHERE
			switch(true) {
				case is_numeric($args["id"]):
					$sql_where[] = "id=?";
					$sql_values[] = sanitize($args["id"]);
					break;
					
				case $args["username"]:
					$sql_where[] = "username=?";
					$sql_values[] = sanitize($args["username"]);
					break;
			}
			
			// ORDER
			$sql_order = is_array($sql_order) ? $sql_order : ["username ASC"];
			
			// LIMIT
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : $sql_limit ?: null;
			
			if($sql_select && $sql_from) {
				
			// CHECK IF NECESSARY
				if(is_numeric($args["id"]) && in_array($args["id"], $this->user_list["by_id"])) {
					if(count($this->user_list["by_id"][$args["id"]]) === count($sql_select)) {
						$users = [$this->user_list["by_id"][$args["id"]]];
					}
				}
				
				if(empty($users)) {
					
			// QUERY
					$sql_user = "SELECT ".implode(", ", $sql_select)." FROM ".implode(" ", $sql_from)." ".($sql_where ? "WHERE (".implode(") AND (", $sql_where).")" : null)." ORDER BY ".implode(", ", $sql_order)." ".$sql_limit;
					$stmt_user = $this->pdo->prepare($sql_user);
					$stmt_user->execute($sql_values);
					$users = $stmt_user->fetchAll();
					$num_users = count($users) ?: 0;
					
					// Transform icon values
					for($i=0; $i<$num_users; $i++) {
						if(isset($users[$i]['icon'])) {
							$icon_num = $users[$i]['icon'];
							$icon_num = is_numeric($icon_num) ? $icon_num : 0;
							$users[$i]['icon'] = $this->allowed_icons[$icon_num];
						}
					}
					
					//echo $_SESSION['username'] === 'inartistic' ? '***'.print_r($sql_user, true).print_r($sql_values, true) : null;
					
					// ADD'L
					
			// UPDATE USER LIST
					if(is_array($users)) {
						foreach($users as $user) {
							foreach($user as $key => $value) {
								$this->user_list["by_id"][$user["id"]][$key] = $value;
							}
							$this->user_list["by_username"][$user["username"]]["id"] = $user["id"];
						}
					}
				}
				
			// RETURN
				$users = is_array($users) ? $users : [];
				$users = is_numeric($args["id"]) || $args["username"] ? reset($users) : $users;
				
				return $users;
			}
		}
	}
?>