<?php
	include_once('../php/include.php');
	
	class access_user {
		
		public  $pdo;
		private $user_list;
		
		// Set icon choices
		public static $allowed_icons = [
			'crown',
			'heart',
			'star',
			'flower',
			'moon',
		];
		
		// Permissions
		public static $allowed_permissions = [
			
			// Participation
			'can_comment',
			'can_add_profile_links',
			'can_access_beta_features',
			
			// Editing
			'can_add_articles',
			'can_access_drafts',
			'can_add_data',
			'can_add_livehouses',
			'can_bypass_video_approval',
			
			// Moderation
			'can_approve_data',
			'can_delete_data',
			'can_edit_roles',
			'can_edit_permissions',
			'can_edit_attributes',
			
		];
		
		// Default permissions
		public static $default_permissions = [
			
			'can_comment' => 1,
			'can_add_profile_links' => 1,
			
		];
		
		// Roles
		public static $allowed_roles = [
			
			'is_editor' => [
				'can_add_data',
			],
			
			'is_writer' => [
				'can_add_articles',
				'can_access_drafts',
			],
			
			'is_moderator' => [
				'can_approve_data',
				'can_delete_data',
				'can_edit_roles',
			],
			
			'is_admin' => [
				'can_edit_permissions',
				'can_edit_attributes',
			],
			
			'is_vip' => [
				'can_access_beta_features',
			],
			
		];
		
		
		
		// ======================================================
		// Construct
		// ======================================================
		function __construct($pdo) {
			
			// Set up connection
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once('../php/database-connect.php');
			}
			$this->pdo = $pdo;
			
			// Setup empty user list array
			$this->user_list = [
				'by_id' => [],
				'by_username' => []
			];
			
		}
		
		
		
		// ======================================================
		// Get user's role/status
		// ======================================================
		public function check_permissions( $user_id ) {
			
			if( is_numeric($user_id) ) {
				
				// Get permissions JSON and decode it
				$sql_permissions = 'SELECT permissions FROM users WHERE id=? LIMIT 1';
				$stmt_permissions = $this->pdo->prepare($sql_permissions);
				$stmt_permissions->execute([ $user_id ]);
				$permissions = $stmt_permissions->fetchColumn();
				
				// If we have some permissions, decode them into array
				if( strlen($permissions) ) {
					$permissions = json_decode( $permissions, true );
				}
				
				// Make sure we have array, even if it's empty
				$permissions = is_array($permissions) ? $permissions : [];
				
				// If permissions/roles in JSON don't encompass all available ones, make sure the user's JSON is updated
				$num_permissions = count($permissions);
				$num_allowed_permissions = count(self::$allowed_permissions) + count(self::$allowed_roles);
				
				// If necessary, regenerate the user's permissions JSON and fill in gaps
				if( $num_permissions != $num_allowed_permissions ) {
					$permissions = $this->regenerate_permissions( $user_id, $permissions );
				}
				
			}
			
			return $permissions;
			
		}
		
		
		
		// ======================================================
		// Regenerate user's permissions JSON
		// ======================================================
		function regenerate_permissions( $user_id, $current_permissions ) {
			
			if( is_numeric($user_id) ) {
				
				// Make sure we have array, even if it's empty
				$current_permissions = is_array($current_permissions) ? $current_permissions : [];
				
				// Loop through allowed roles first and make sure they're set (some permissions rely on role)
				foreach( self::$allowed_roles as $role => $role_permissions ) {
					
					// If currently set, keep same value
					if( strlen( $current_permissions[ $role ] ) ) {
						$new_permissions[ $role ] = $current_permissions[ $role ];
					}
					
					// If not set, default to false
					else {
						$new_permissions[ $role ] = 0;
					}
					
					// If the user has this role, then let's default all associated permissions to true, and revise these in the next step
					if( $new_permissions[ $role ] ) {
						foreach( $role_permissions as $permission ) {
							$new_permissions[ $permission ] = 1;
						}
					}
					
				}
				
				// Next, loop through allowed permissions and make sure they're set
				foreach( self::$allowed_permissions as $permission ) {
					
					// If currently set in database, keep same value (this will overwrite values auto set by associated roles)
					if( isset( $current_permissions[ $permission ] ) ) {
						$new_permissions[ $permission ] = $current_permissions[ $permission ];
					}
					
					// If not set, check if there's a default and set it
					else {
						
						// If default is set, use that
						if( isset( self::$default_permissions[ $permission ] ) ) {
							$new_permissions[ $permission ] = self::$default_permissions[ $permission ];
						}
						
						// Otherwise assume permission is false
						else {
							$new_permissions[ $permission ] = 0;
						}
						
					}
					
				}
				
				// Now save the new permissions back to JSON and update the DB
				$sql_update = 'UPDATE users SET permissions=? WHERE id=? LIMIT 1';
				$stmt_update = $this->pdo->prepare($sql_update);
				$stmt_update->execute([ json_encode( $new_permissions ), $user_id ]);
				
			}
			
			// Make sure we return an array
			$new_permissions = is_array($new_permissions) ? $new_permissions : [];
			return $new_permissions;
			
		}
		
		
		
		// ======================================================
		// Change user's permission
		// ======================================================
		public function change_permission( $user_id, $permission_name, $give_permission = false ) {
			
			if( is_numeric($user_id) && ( in_array($permission_name, self::$allowed_permissions) || in_array($permission_name, array_keys(self::$allowed_roles) ) ) ) {
				
				// Get current permissions
				$current_permissions = $this->check_permissions($user_id);
				
				// Change specified permissions
				$new_permissions = $current_permissions;
				$new_permissions[ $permission_name ] = $give_permission ? 1 : 0;
				
				// Save permissions
				$sql_new = 'UPDATE users SET permissions=? WHERE id=? LIMIT 1';
				$stmt_new = $this->pdo->prepare($sql_new);
				if( $stmt_new->execute([ json_encode($new_permissions), $user_id ]) ) {
					$output['status'] = 'success';
				}
				else {
					$output['result'] = tr('Couldn\'t update permission.');
				}
				
			}
			else {
				$output['result'] = tr('Permission information is missing.');
			}
			
			return $output;
			
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