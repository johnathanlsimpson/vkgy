<?php

	class login {
		private $newHash;
		private $hashInsertID;
		private $pdo;
		private $domain;
		private $secret_key;
		public  $status;
		
		
		
		// ======================================================
		// Construct DB connection
		// ======================================================
		function __construct($pdo) {
			$this->pdo = $pdo;
			if(!$this->pdo) {
				$this->status = 1;
			}
			
			$this->domain = "vk.gy";
			
			include_once('../php/class-login-key.php');
			$this->secret_key = $secret_key;
			
			// User object
			$this->access_user = new access_user($this->pdo);
			
		}
		
		
		
		// ======================================================
		// Set hash cookie to enable persistent login
		// ======================================================
		private function hashSet($user_id) {
			if(is_numeric($user_id)) {
				
				$user_agent = substr(friendly($_SERVER['HTTP_USER_AGENT']), 0, 255);
				$remote_addr = sanitize($_SERVER['REMOTE_ADDR']);
				$token = bin2hex(random_bytes(16));
				
				// Clear previous token (if it matches this IP/browser) and also clear any really old tokens (regardless of IP/browser)
				$sql_clear_tokens = "DELETE FROM users_tokens WHERE user_id=? AND ( (remote_addr=? AND user_agent=?) OR date_occurred <= CURRENT_DATE() - INTERVAL 1 MONTH )";
				$stmt_clear_tokens = $this->pdo->prepare($sql_clear_tokens);
				$stmt_clear_tokens->execute([ $user_id, $user_agent, $remote_addr ]);
				
				// Insert new token
				$sql_token = "INSERT INTO users_tokens (user_id, token, user_agent, remote_addr) VALUES (?, ?, ?, ?)";
				$stmt_token = $this->pdo->prepare($sql_token);
				if($stmt_token->execute([ $user_id, $token, $user_agent, $remote_addr ])) {
					
					// Create cookie for persistent login
					$cookie = $user_id.":".$user_agent.':'.$remote_addr.":".$token;
					$mac = hash_hmac("sha256", $cookie, $this->secret_key);
					$cookie .= ":".$mac;
					setcookie("remember_me", $cookie, time() + 60*60*24*30, "/", $this->domain, true, true);
					
					return true;
					
				}
				else {
					$this->status = 2;
					return false;
				}
			}
			else {
				return false;
			}
		}
		
		
		
		// Check 'hash' cookie to sign in
		private function hashCheck($cookie) {
			if($cookie) {
				list($user_id, $user_agent, $remote_addr, $token, $mac) = explode(":", $cookie);
				
				if(!empty($user_id) && !empty($remote_addr) && !empty($token) && !empty($mac)) {
					if(!hash_equals(hash_hmac("sha256", $user_id.':'.$user_agent.":".$remote_addr.":".$token, $this->secret_key), $mac)) {
						return false;
					}
					else {
						$sql_token = "SELECT 1 FROM users_tokens WHERE user_id=? AND user_agent=? AND remote_addr=? AND token=? AND date_occurred >= CURRENT_DATE() - INTERVAL 1 MONTH";
						$stmt_token = $this->pdo->prepare($sql_token);
						$stmt_token->execute([$user_id, $user_agent, $remote_addr, $token]);
						if($stmt_token->fetchColumn()) {
							$this->hashSet($user_id);
							return true;
						}
						else {
							$this->status = 3;
							return false;
						}
					}
				}
				else {
					$this->status = 11;
					return false;
				}
			}
			else {
				$this->status = 4;
				return false;
			}
		}
		
		
		
		// ======================================================
		// Set user's role/status
		// ======================================================
		public function set_permissions($user_status) {
			
			if(is_array($user_status) && !empty($user_status)) {
				foreach($user_status as $status_type => $status) {
					$_SESSION[$status_type] = $status;
				}
			}
			
		}
		
		
		
		// ======================================================
		// Check if signed in via session -> check cookie ->
		// create new has cookie, update session
		// ======================================================
		public function check_login() {
			if($_SESSION["is_signed_in"]) {
				if( !strlen($_SESSION['site_lang']) || !strlen($_SESSION['site_theme']) || !strlen($_SESSION['site_point_animations']) ) {
					$sql_prefs = 'SELECT site_lang, site_theme, site_point_animations FROM users WHERE id=? LIMIT 1';
					$stmt_prefs = $this->pdo->prepare($sql_prefs);
					$stmt_prefs->execute([ $_SESSION['user_id'] ]);
					$rslt_prefs = $stmt_prefs->fetch();
					
					if(is_array($rslt_prefs) && !empty($rslt_prefs)) {
						$_SESSION['site_lang'] = $rslt_prefs['site_lang'];
						$_SESSION['site_theme'] = $rslt_prefs['site_theme'];
						$_SESSION['site_point_animations'] = $rslt_prefs['site_point_animations'];
					}
				}
				
				return true;
				$this->status = 5;
			}
			else {
				if($_COOKIE["remember_me"] && $this->hashCheck($_COOKIE["remember_me"])) {
					list($user_id, $user_agent, $remote_addr, $token, $mac) = explode(":", $_COOKIE["remember_me"]);
					
					if(is_numeric($user_id)) {
						$sql_user = "SELECT id, username, site_theme, site_lang, site_point_animations FROM users WHERE id=? LIMIT 1";
						$stmt_user = $this->pdo->prepare($sql_user);
						$stmt_user->execute([$user_id]);
						$row = $stmt_user->fetch();
						
						if(is_array($row) && !empty($row)) {
							
							$session_data = [
								'user_id' => $row['id'],
								'username' => $row['username'],
								'site_theme' => $row['site_theme'],
								'site_lang' => $row['site_lang'],
								'site_point_animations' => $row['site_point_animations'],
								'is_signed_in' => 1,
							];
							
							// Get user's roles and permissions, then set them for this session
							$this->set_permissions( $this->access_user->check_permissions( $row['id'] ) );
							
							$this->set_login_data($session_data);
							
						}
					}
					
					return true;
					$this->status = 5;
				}
				else {
					return false;
					$this->status = 6;
				}
			}
		}
			
			
			
			// Take info gathered from DB during sign in, apply to $_SESSION, and then set current IP address in DB
			public function set_login_data($user_data) {
				$sql_set_ip = 'UPDATE users SET ip_address=? WHERE id=? LIMIT 1';
				$stmt_set_ip = $this->pdo->prepare($sql_set_ip);
				$stmt_set_ip->execute([ ip2long($_SERVER['REMOTE_ADDR']), $user_data['user_id'] ]);
				
				$_SESSION = is_array($_SESSION) ? $_SESSION : [];
				$_SESSION = array_merge($_SESSION, $user_data);
				
				$this->hashSet($user_data["user_id"]);
			}
			
			
			
			// Sign in from post
			public function sign_in($input) {
				$username_pattern = "^[\w\-\.\ ]{3,}$";
				if(preg_match("/".$username_pattern."/", $input["username"])) {
					$sql_user = "SELECT id, username, rank, is_vip, icon, password_old, password, site_theme, site_lang, site_point_animations FROM users WHERE username=? LIMIT 1";
					$stmt = $this->pdo->prepare($sql_user);
					$stmt->execute(array($input["username"]));
					$row = $stmt->fetch();
					
					if($row) {
						$session_data = [
							'user_id' => $row['id'],
							'site_theme' => $row['site_theme'],
							'is_signed_in' => 1,
							'user_id' => $row['id'],
							'username' => $row['username'],
							'site_theme' => $row['site_theme'],
							'site_lang' => $row['site_lang'],
							'site_point_animations' => $row['site_point_animations'],
							'is_signed_in' => 1,
						];
						
						// Set user role/VIP status
						$this->set_permissions( $this->access_user->check_permissions( $row['id'] ) );
						
						// If using old password
						if(strlen($row["password_old"]) > 0 && empty($row["password"])) {
							if(
								password_verify(md5($input["password"]), $row["password_old"])
							) {
								$sql_update_password = "UPDATE users SET password=?, password_old=NULL WHERE id=? LIMIT 1";
								$stmt_update_password = $this->pdo->prepare($sql_update_password);
								if($stmt_update_password->execute([ 
									password_hash($input["password"], PASSWORD_DEFAULT),
									$row["id"]
								])) {
									$this->set_login_data($session_data);
									$this->status = 7;
								}
								else {
									$this->sign_out();
									$this->status = 9;
								}
							}
						}
						
						
						elseif(strlen($row["password"]) > 0) {
							if(password_verify($input["password"], $row["password"])) {
								$this->set_login_data($session_data);
								$this->status = 7;
							}
							else {
								$this->sign_out();
								$this->status = 9;
							}
						}
					}
					else {
						$this->sign_out();
						$this->status = 9;
					}
				}
				else {
					$this->sign_out();
					$this->status = 10;
				}
			}
			
			
			
			// Sign out: destroy session and cookie
			public function sign_out() {
				session_start();
				
				$_SESSION = [];
				
				foreach(["user_id", 'user_id', "username", "admin", 'is_admin', "icon", "hash", "is_signed_in", 'loggedIn', 'is_vip', 'site_theme', 'site_theme', 'site_lang', "remember_me"] as $key) {
					//unset($_SESSION[$key]);
					setcookie($key, "", time() - 60 * 60 * 24 * 40, "/", $this->domain, true, true);
				}
				
				session_destroy();
				
				$this->status = 8;
			}
			
			
			
			// Result messages
			public function get_status_message() {
				$status_messages = [
					1  => "Database connection could not be established.",
					2  => "New hash could not be created.",
					3  => "Error checking hash: username or hash empty.",
					4  => "Error checking hash.",
					5  => "Already signed in.",
					6  => "Not signed in.",
					7  => "Signed in.",
					8  => "Signed out.",
					9  => "Username or password incorrect.",
					10 => "Username may only contain letters, numbers, underscores, hyphens, and/or periods.",
					11 => "Cookie has empty strings.",
					12 => "Password could not be verified.",
				];
				if(is_numeric($this->status)) {
					return $status_messages[$this->status];
				}
			}
		}
?>