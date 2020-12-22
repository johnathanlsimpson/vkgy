<?php
	include_once('../php/include.php');
	
	class access_user {
		public  $pdo;
		public  $permissions;
		public  $allowed_permissions;
		public  $allowed_icons;
		private $user_list;
		
		
		
		// ======================================================
		// Construct
		// ======================================================
		function __construct($pdo) {
			
			// Set up connection
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once('../php/database-connect.php');
			}
			$this->pdo = $pdo;
			
			// Set icon choices
			$this->allowed_icons = [
				'crown',
				'heart',
				'star',
				'flower',
				'moon',
			];
			
			// Setup empty user list array
			$this->user_list = [
				'by_id' => [],
				'by_username' => []
			];
			
			// Permissions
			$this->permissions = [
				'participation' => [
					'can_comment',
					'can_access_beta_features',
				],
				
				'editing' => [
					'can_add_data',
					'can_add_livehouses',
					'can_access_drafts',
					'can_bypass_video_approval',
				],
				
				'moderation' => [
					'can_approve_data',
					'can_delete_data',
					'can_edit_roles',
					'can_edit_permissions',
				],
			];
			
			// Quick list of allowed permissions
			$this->allowed_permissions = [];
			foreach($this->permissions as $permission_group) {
				
				$this->allowed_permissions = array_merge($this->allowed_permissions, $permission_group);
				
			}
			
			// Roles
			$this->allowed_roles = [
				'editor' => [
					'can_add_data',
				],
				'moderator' => [
					'can_approve_data',
					'can_delete_data',
					'can_edit_roles',
				],
				'vip' => [
					'can_access_drafts',
					'can_access_beta_features',
				],
			];
			
		}
		
		
		
		// ======================================================
		// Get user's role/status
		// ======================================================
		public function check_permissions( $user_id ) {
			
			if( is_numeric($user_id) ) {
				
				// Get user's data (eventually we'll just get the is_xxx and permissions columns, but
				// for now we have to deal with legacy users that had columns for each permissions)
				$sql_user = '
					SELECT
						permissions,
						can_add_data,
						can_add_livehouses,
						can_delete_data,
						can_approve_data,
						can_comment,
						can_access_drafts,
						can_edit_roles,
						can_edit_permissions,
						is_vip,
						is_editor,
						is_moderator,
						is_boss
					FROM users WHERE id=? LIMIT 1';
				$stmt_user = $this->pdo->prepare($sql_user);
				$stmt_user->execute([ $user_id ]);
				$rslt_user = $stmt_user->fetch();
				
				// Get permissions string
				$permissions = json_decode($rslt_user['permissions'], true);
				$permissions = is_array($permissions) && !empty($permissions) ? $permissions : [];
				unset($rslt_user['permissions']);
				
				// Return roles and permissions
				$roles_and_permissions = array_merge($rslt_user, $permissions);
				return $roles_and_permissions;
				
			}
			
		}
		
		
		
		// ======================================================
		// Change user's permission
		// ======================================================
		public function change_permission( $user_id, $permission_name, $give_permission = false ) {
			
			if( is_numeric($user_id) && in_array($permission_name, $this->allowed_permissions) ) {
				
				// Get current permissions
				$current_permissions = $this->check_permissions($user_id);
				
				// Change specified permissions
				$new_permissions = $current_permissions;
				$new_permissions[$permission_name] = $give_permission ? 1 : 0;
				
				// Save permissions
				$sql_new = 'UPDATE users SET permissions=? WHERE id=? LIMIT 1';
				$stmt_new = $this->pdo->prepare($sql_new);
				$stmt_new->execute([ json_encode($new_permissions), $user_id ]);
				
			}
			
		}
		
		
		
		// ======================================================
		// Render default username link
		// ======================================================
		public function render_username($user_data, $class = null) {
			if(is_array($user_data) && !empty($user_data) && strlen($user_data['username'])) {
				
				$output =
					'<a '.
					'class="user'.($class ? ' '.$class : null).'" '.
					'data-icon="'.$user_data['icon'].'" data-is-editor="'.$user_data['is_editor'].'" data-is-moderator="'.$user_data['is_moderator'].'" data-is-vip="'.$user_data['is_vip'].'" '.
					'href="'.($user_data['url'] ?: '/users/'.$user_data['username'].'/').'">'.
					$user_data['username'].
					'<span class="user__moderator"></span>'.
					'<span class="user__editor"></span>'.
					'<span class="user__vip"></span>'.
					'</a>';
				
				return $output;
			}
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
			switch($args['get']) {
				
				case 'all' :
					$sql_select = [
						'users.id',
						'users.username',
						'users.email',
						'users.icon',
						'users.is_vip',
						'users.is_editor',
						'users.is_moderator',
						'users.is_boss',
						'users.name',
						'users.motto',
						'users.website',
						'users.twitter',
						'users.facebook',
						'users.lastfm',
						'users.mh',
						'users.birthday',
						'users.pronouns',
						'users.date_added',
						'users.fan_since',
						'users.site_theme',
						'CONCAT_WS("/", "", "users", users.username, "") AS url',
						'users.permissions',
					];
					break;
				
				default:
					$sql_select = [
						'users.id',
						'users.username',
						'users.icon',
						'users.is_vip',
						'users.is_editor',
						'users.is_moderator',
						'users.is_boss',
						'users.date_added',
						'CONCAT_WS("/", "", "users", users.username, "") AS url',
						'CONCAT("/usericons/avatar-", users.username, ".png") AS avatar_url'
					];
				
			}
			
			// FROM
			$sql_from = is_array($sql_from) ? $sql_from : [ 'users' ];
			
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
					
				case $args['is_vip']:
					$sql_where[] = 'is_vip=?';
					$sql_values[] = 1;
					break;
			}
			
			// ORDER
			$sql_order = is_array($args['order']) ? $args['order'] : ( strlen($args['order']) ? [ $args['order'] ] : null);
			$sql_order = is_array($sql_order) ? $sql_order : [ 'username ASC' ];
			
			// LIMIT
			$sql_limit = preg_match("/"."[\d ,]+"."/", $args["limit"]) ? "LIMIT ".$args["limit"] : ($sql_limit ?: null);
			
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