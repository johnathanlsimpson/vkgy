<?php
	include_once('../php/include.php');
	include_once('../php/external/class-gumletImageResize.php');
	
	class access_image {
		private $pdo;
		private $resize_methods;
		private $allowed_resize_methods;
		private $image_paths;
		private $font_source;
		private $site_credit;
		
		// Allowed extensions
		static public $allowed_extensions = [
			'gif',
			'jpeg',
			'jpg',
			'png',
			'webp',
		];
		
		// Image contents
		static public $allowed_image_contents = [
			1 => 'group photo',
			2 => 'musician',
			3 => 'flyer',
			4 => 'logo',
			5 => 'release',
			0 => 'other',
		];
		
		// Image format ratios (short side / long side)
		static public $image_ratios = [
			'0.70' => 'flyer',
			'0.66' => 'musician',
		];
		
		// ======================================================
		// Connect
		// ======================================================
		function __construct($pdo) {
			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
				include_once('../php/database-connect.php');
			}
			
			$this->pdo = $pdo;
			
			$this->access_user = new access_user($this->pdo);
			
			$this->resize_methods = ['thumbnail' => 100, 'small' => 150, 'medium' => 300, 'large' => 400, 'watermark' => 800, 'full' => 0];
			
			$this->allowed_resize_methods = array_keys($this->resize_methods);
			
			foreach($this->resize_methods as $key => $value) {
				$this->image_paths[$key] = '../images/image_files'.($key != 'full' ? '_'.($key === 'watermark' ? 'watermarked' : $key) : null).'/';
			}
			
			$this->font_source = '../style/font-lucida.ttf';
			
			$this->site_credit = 'vk.gy';
		}
		
		// ======================================================
		// Temporarily increase memory limit
		// ======================================================
		function set_memory_limit($source_image_path) {
			// Set time limit
			set_time_limit(50);
			
			// Set max memory usage in MB
			if($_SESSION['is_signed_in']) {
				$max_memory_size = 400;
			}
			else {
				$max_memory_size = 200;
			}
			
			// Current memory limit
			$curr_max_memory_size = ini_get('memory_limit');
			
			// Get the image width and height
			$height = 0;
			$width = 0;
			list($width, $height) = getimagesize($source_image_path);
			
			// Calculate needed memory
			$new_memory_size = $curr_max_memory_size + floor(($width * $height * 4 * 1.5 + 1048576) / 1048576);
			
			if($new_memory_size < $max_memory_size) {
				if($new_memory_size > $curr_max_memory_size) {
					// Update memory limit
					if(ini_set('memory_limit', $new_memory_size.'M') === false) {
						return false;
					}
					else {
						return true;
					}
				}
				else {
					// No increase needed
					return true;
				}
			}
			else {
				// File too large
				return false;
			}
		}
		
		// ======================================================
		// Watermark image
		// ======================================================
		function get_watermarked_image($source_id, $source_extension) {
			// Set up watermarked path
			$watermarked_path = $this->image_paths['watermark'].$source_id.'.'.'jpg';
			
			// If file already exist, watermarked image has already been created
			if(file_exists($watermarked_path)) {
				$output_image_path = $watermarked_path;
			}
			
			// Otherwise, let's make the watermarked version
			else {
				
				// Resize to watermark size
				$resized_image = $this->get_resized_image($source_id, $source_extension, 'watermark');
				
				// If resized, go ahead
				if(strlen($resized_image) && file_exists($resized_image)) {
					
					// Make sure resized image is in correct location
					if($resized_image === $watermarked_path) {
						
						// Make sure file is actually jpg like expected
						if(strtolower(pathinfo($watermarked_path, PATHINFO_EXTENSION)) === 'jpg') {
							
							// Get username of uploader
							$sql_user = "SELECT users.username FROM images LEFT JOIN users ON users.id=images.user_id WHERE images.id=? LIMIT 1";
							$stmt_user = $this->pdo->prepare($sql_user);
							if($stmt_user->execute([ $source_id ])) {
								$username = $stmt_user->fetchColumn();
							}
							
							// Create image object
							if($watermarked_image = imagecreatefromjpeg($watermarked_path)) {
							}
							
							// Watermark image object
							if(is_resource($watermarked_image)) {
								
								// Get dimensions of image object and watermark object
								$height = imagesy($watermarked_image);
								$width = imagesx($watermarked_image);
								$watermark_size = imagettfbbox(16, 0, $this->font_source, $this->site_credit);
								$user_watermark_size = imagettfbbox(12, 0, $this->font_source, ($username ?: ' '));
								
								// Apply watermark with text shadow
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] + 1, $height - 35 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $this->site_credit);
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] - 1, $height - 35 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $this->site_credit);
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] + 1, $height - 35 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $this->site_credit);
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2] - 1, $height - 35 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $this->site_credit);
								imagettftext($watermarked_image, 16, 0, $width - 15 - $watermark_size[2], $height - 35, imagecolorallocatealpha($watermarked_image, 255,255,255, 0), $this->font_source, $this->site_credit);
								
								// If username provided, apply username watermark with text shadow
								if(!empty($username)) {
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] + 1, $height - 15 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] + 1, $height - 15 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] - 1, $height - 15 + 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2] - 1, $height - 15 - 1, imagecolorallocatealpha($watermarked_image, 0,0,0, 63), $this->font_source, $username);
									imagettftext($watermarked_image, 12, 0, $width - 15 - $user_watermark_size[2], $height - 15, imagecolorallocatealpha($watermarked_image, 255,255,255, 0), $this->font_source, $username);
								}
								
								// Save watermarked image, return url if successful
								if(imagejpeg($watermarked_image, $watermarked_path, 100)) {
									$output_image_path = $watermarked_path;
								}
								
								// If image could not be watermarked or saved... try to show large, then 404?
								else {
									$output_image_path = $this->image_paths['full'].$source_id.'.'.'jpg';
								}
							}
						}
						else {
							// Image not jpg
						}
					}
					else {
						// Image was resized to a different location
					}
				}
				else {
					// Couldn't be resized, try to show large, then 404?
				}
			}
			
			return $output_image_path;
		}
		
		// ======================================================
		// Resize image
		// ======================================================
		function get_resized_image($source_id, $source_extension, $resize_method) {
			if(is_numeric($source_id)) {
				
				// If unallowed resize method, set default
				if(!strlen($resize_method) || !in_array($resize_method, $this->allowed_resize_methods)) {
					$resize_method = 'medium';
				}
				
				$full_path = $this->image_paths['full'].$source_id.'.'.$source_extension;
				$resized_path = $this->image_paths[$resize_method].$source_id.'.'.'jpg';
				
				if(file_exists($full_path)) {
					
					// If gif, don't resize
					if($source_extension === 'gif') {
						$output_image_path = $full_path;
					}
					
					// If already resized, just return that
					elseif(file_exists($resized_path)) {
						$output_image_path = $resized_path;
					}
					
					// Otherwise, let's resize it
					elseif(strlen($source_extension) && in_array($source_extension, self::allowed_extensions)) {
						
						// Increase memory limit if necessary
						if($this->set_memory_limit($full_path)) {
							
							// Get the image width and height
							$height = 0;
							$width = 0;
							list($width, $height) = getimagesize($full_path);
							
							// If even worth resizing
							if($height > $this->resize_methods[$resize_method] || $width > $this->resize_methods[$resize_method]) {
								
								// Make image object, resize
								$resized_image = new \Gumlet\ImageResize($full_path);
								
								// If image resizer called successfully
								if($resized_image) {
									
									// Find appropriate method, resize appropriately, set quality for later
									if($resize_method === 'thumbnail') {
										if($resized_image->resizeToWidth(100)) {
											$quality = 80;
										}
									}
									elseif($resize_method === 'small') {
										if($resized_image->resizeToBestFit(150, 150)) {
											$quality = 80;
										}
									}
									elseif($resize_method === 'medium') {
										if($resized_image->resizeToWidth(300)) {
											$quality = 80;
										}
									}
									elseif($resize_method === 'large') {
										if($resized_image->resizeToBestFit(600, 400)) {
											$quality = 90;
										}
									}
									elseif($resize_method === 'watermark') {
										if($resized_image->resizeToBestFit(800, 800)) {
											$quality = 100;
										}
									}
									
									// Resize as jpg
									if($resized_image->save($resized_path, IMAGETYPE_JPEG, $quality)) {
										
										// Return new image path
										$output_image_path = $resized_path;
									}
									else {
										// Couldn't resize/save, return original path
										$output_image_path = $full_path;
									}
								}
								else {
									// Couldn't make image object
									$output_image_path = $full_path;
								}
							}
							else {
								// No need to resize
								$output_image_path = $full_path;
							}
						}
						else {
							// File too big
						}
					}
					else {
						// Extension can't be resized
					}
				}
				else {
					// File doesn't exist
				}
			}
			else {
				// No image url provided
			}
			
			return $output_image_path;
		}
		
		// ======================================================
		// Check if user has permission to view full size
		// ======================================================
		function get_full_size_image($source_id, $source_extension) {
			$full_path = $this->image_paths['full'].$source_id.'.'.$source_extension;
			
			// Check if image is VIP
			$sql_image = 'SELECT is_exclusive, user_id FROM images WHERE id=? LIMIT 1';
			$stmt_image = $this->pdo->prepare($sql_image);
			$stmt_image->execute([ $source_id ]);
			$rslt_image = $stmt_image->fetch();
			
			// Check user VIP status
			if(is_numeric($_SESSION['user_id'])) {
				$sql_user = "SELECT 1 FROM users WHERE id=? AND is_vip=? LIMIT 1";
				$stmt_user = $this->pdo->prepare($sql_user);
				$stmt_user->execute([ $_SESSION["user_id"], 1]);
				$user_is_vip = $stmt_user->fetchColumn() === "1";
			}
			
			// Check if VIP user using Mp3tag
			if(stripos($_SERVER["HTTP_USER_AGENT"], 'mp3tag') !== false && strlen($_GET['username']) && strlen($_GET['hash'])) {
				$sql_mp3tag = "SELECT 1 FROM users WHERE username=? AND tag_hash=? LIMIT 1";
				$stmt_mp3tag = $this->pdo->prepare($sql_mp3tag);
				$stmt_mp3tag->execute([ sanitize($_GET['username']), sanitize($_GET['hash']) ]);
				$mp3tag_is_vip = $stmt_mp3tag->fetchColumn() === "1";
			}
			
			// If image was found in DB
			if(is_array($rslt_image) && !empty($rslt_image)) {
				$image_is_exclusive = $rslt_image['is_exclusive'] ? true : false;
				$user_is_uploader = $_SESSION['user_id'] === $rslt_image['user_id'];
				
				// If image exclusive
				if($image_is_exclusive) {
					// Cycle through conditions to see if user has permission
					if(
						(!$image_is_exclusive) ||
						($image_is_exclusive && $user_is_uploader) ||
						($image_is_exclusive && $user_is_vip) ||
						($image_is_exclusive && $mp3tag_is_vip)
					) {
						// Return full size image
						$output_image_path = $full_path;
					}
					else {
						// Show watermarked image
						if($output_image_path = $this->get_watermarked_image($source_id, $source_extension)) {
							
						}
						else {
							// Couldn't watermark
							$output_image_path = $full_path;
						}
					}
				}
				else {
					// Not exclusive, show full size image
					$output_image_path = $full_path;
				}
			}
			else {
				// Image not found in DB
			}
			
			return $output_image_path;
		}
		
		// ======================================================
		// Find default artist image
		// ======================================================
		function get_artist_image_id($artist_friendly) {
			// If artist ID provided, look for main artist image 
			if(strlen($artist_friendly)) {
				$sql_artist = "SELECT images.id AS image_id FROM artists LEFT JOIN images ON images.id=artists.image_id WHERE artists.friendly=? LIMIT 1";
				$stmt_artist = $this->pdo->prepare($sql_artist);
				$stmt_artist->execute([ sanitize($artist_friendly) ]);
				$rslt_artist = $stmt_artist->fetchColumn();
			}
			
			return $rslt_artist;
		}
		
		// ======================================================
		// Render image
		// ======================================================
		function render_image($image_path) { 
			if(strlen($image_path) && file_exists($image_path)) {
				$ext = end(explode('.', $image_path));
				
				session_cache_limiter('public');
				
				header('Content-Type: image/'.$ext);
				header('Content-Length: '.filesize($image_path));
				header('Expires: '.gmdate('D, d M Y H:i:s', time() + 120).' GMT');
				header('Cache-Control: max-age=120');
				
				readfile($image_path);
			}
			else {
				$this->no_image();
			}
		}
		
		// ======================================================
		// Handle missing images
		// ======================================================
		function no_image() {
			header('Location: /404/');
		}
		
		// ======================================================
		// Get image
		// ======================================================
		function get_image($input) {
			if(is_array($input) && !empty($input)) {
				
				// If requesting artist image, find ID
				if(strlen($input['artist'])) {
					$input['id'] = $this->get_artist_image_id($input['artist']);
				}
				
				// Sanitize inputs
				$image_id = sanitize($input['id']);
				$resize_method = sanitize($input['method']) ?: 'full';
				
				// If proper image ID provided
				if(is_numeric($image_id)) {
					
					// Get actual extension
					$sql_image = 'SELECT extension FROM images WHERE id=? LIMIT 1';
					$stmt_image = $this->pdo->prepare($sql_image);
					$stmt_image->execute([ $image_id ]);
					$image_ext = $stmt_image->fetchColumn();
					
					// If extension found
					if(strlen($image_ext) && in_array($image_ext, self::allowed_extensions)) {
						
						if($resize_method === 'full') {
							$image_url = $this->get_full_size_image($image_id, $image_ext);
						}
						else {
							$image_url = $this->get_resized_image($image_id, $image_ext, $resize_method);
						}
					}
					else {
						// Couldn't get extension, 404
					}
				}
				else {
					// No image ID supplied, can't show image
				}
			}
			else {
				// No input
			}
			
			// Return URL only if requested
			if($input['image_path_only']) {
				return $image_url;
			}
			else {
				$this->render_image($image_url);
			}
		}
		
		// ======================================================
		// Core function
		// ======================================================
		function access_image($args = []) {
			
			// Setup
			$sql_select = [];
			$sql_from;
			$sql_join = [];
			$sql_where = [];
			$sql_group = [];
			$sql_order = [];
			$sql_limit;
			$sql_values = [];
			
			// Select
			if($args['get'] === 'all' || $args['get'] === 'most') {
				$sql_select[] = 'images.*';
				$sql_select[] = 'CONCAT("/images/", images.id, "-", COALESCE(images.friendly, "image"), ".", images.extension) AS url';
				$sql_select[] = 'GROUP_CONCAT(DISTINCT images_artists.artist_id) AS artist_ids';
				$sql_select[] = 'GROUP_CONCAT(DISTINCT images_blog.blog_id) AS blog_id';
				$sql_select[] = 'GROUP_CONCAT(DISTINCT images_labels.label_id) AS label_ids';
				$sql_select[] = '"" AS musician_ids';
				$sql_select[] = 'GROUP_CONCAT(DISTINCT images_releases.release_id) AS release_ids';
			}
			if($args['get'] === 'name') {
				$sql_select[] = 'images.id';
				$sql_select[] = 'images.extension';
				$sql_select[] = 'images.friendly';
				$sql_select[] = 'CONCAT("/images/", images.id, "-", COALESCE(images.friendly, "image"), ".", images.extension) AS url';
			}
			
			// From
			if($args['flyer_of_day']) {
				$sql_from = 'queued_fod';
			}
			foreach(['artist_id' => 'artists', 'blog_id' => 'blog', 'label_id' => 'labels', 'musician_id' => 'musicians', 'release_id' => 'releases'] as $id_type => $id_table) {
				if(is_array($args[$id_type]) || strlen($args[$id_type])) {
					$args[$id_type] = is_array($args[$id_type]) ? $args[$id_type] : [ $args[$id_type] ];
					
					if($args['default']) {
						$sql_from = '(SELECT image_id FROM '.$id_table.' WHERE ('.substr(str_repeat('id=? OR ', count($args[$id_type])), 0, -4).')) inner_join';
					}
					else {
						$sql_from = '(SELECT image_id FROM images_'.$id_table.' WHERE ('.substr(str_repeat($id_type.'=? OR ', count($args[$id_type])), 0, -4).') ORDER BY id DESC) inner_join';
					}
					
					$sql_values = array_merge($sql_values, $args[$id_type]);
				}
			}
			if($args['type'] === 'artist') {
				$sql_from = '(SELECT image_id FROM images_artists'.($args['order'] ? ' ORDER BY '.str_replace('images.', 'images_artists.', $args['order']) : null).($args['limit'] ? ' LIMIT '.$args['limit'] : null).') inner_join';
			}
			if($args['type'] === 'release') {
				$sql_from = '(SELECT image_id FROM images_releases'.($args['order'] ? ' ORDER BY '.str_replace('images.', 'images_releases.', $args['order']) : null).($args['limit'] ? ' LIMIT '.$args['limit'] : null).') inner_join';
			}
			if(!$sql_from) {
				$sql_from = 'images';
			}
			
			// Join
			if($args['flyer_of_day']) {
				$sql_join[] = 'LEFT JOIN images ON images.id=queued_fod.image_id';
			}
			if(substr($sql_from, -10) === 'inner_join') {
				$sql_join[] = 'INNER JOIN images ON images.id=inner_join.image_id';
			}
			if($args['get'] === 'all' || $args['get'] === 'most') {
				$sql_join[] = 'LEFT JOIN images_artists ON images_artists.image_id=images.id';
				$sql_join[] = 'LEFT JOIN images_blog ON images_blog.image_id=images.id';
				$sql_join[] = 'LEFT JOIN images_labels ON images_labels.image_id=images.id';
				$sql_join[] = 'LEFT JOIN images_releases ON images_releases.image_id=images.id';
			}
			
			// Where
			if($args['type'] === 'flyer') {
				$sql_where[] = 'images.description LIKE "%flyer%"';
			}
			if($args['type'] === 'vip') {
				$sql_where[] = 'images.is_exclusive=?';
				$sql_values[] = 1;
			}
			if(!$args['show_queued']) {
				$sql_where[] = 'images.is_queued=?';
				$sql_values[] = 0;
			}
			
			// Group
			if($args['get'] === 'all' || $args['get'] === 'most') {
				$sql_group[] = 'images.id';
			}
			
			// Order
			if($args['order']) {
				$sql_order[] = $args['order'];
			}
			elseif($args['get'] === 'all' || $args['get'] === 'most') {
				$sql_order[] = 'images.id DESC';
			}
			
			// Limit
			$sql_limit = $args['limit'] ?: null;
			
			// Prepare query
			$sql_images =
				'SELECT '.implode(', ', $sql_select).' '.
				'FROM '.$sql_from.' '.
				(implode(' ', $sql_join)).' '.
				(!empty($sql_where) ? 'WHERE ('.implode(') AND (', $sql_where).')' : null).' '.
				(!empty($sql_group) ? 'GROUP BY '.implode(', ', $sql_group) : null).' '.
				(!empty($sql_order) ? 'ORDER BY '.implode(', ', $sql_order) : null).' '.
				($sql_limit ? 'LIMIT '.$sql_limit : null);
			$stmt_images = $this->pdo->prepare($sql_images);
			
			// Run query
			if(substr_count($sql_images, '?') === count($sql_values)) {
				if($stmt_images->execute( $sql_values )) {
					$images = $stmt_images->fetchAll();
					$num_images = count($images);
					
					// Get musicians which are tagged generally and/or by face
					if( $args['get'] === 'all' || $args['get'] === 'most' ) {
						
						// Loop through images and save all IDs
						// Also create empty skeleton with detected faces
						for($i=0; $i<$num_images; $i++) {
							
							$image_ids[] = $images[$i]['id'];
							
							// Make empty musicians array from any present face boundaries--later we'll fill in the musician ID if it's been tagged
							if( $images[$i]['face_boundaries'] ) {
								
								$face_boundaries = json_decode( $images[$i]['face_boundaries'], true );
								
								if( is_array($face_boundaries) && !empty($face_boundaries) ) {
									
									foreach($face_boundaries as $face_boundary) {
										
										$face_boundary = json_encode($face_boundary);
										$images[$i]['musicians'][ $face_boundary ] = [ 'musician_id' => null, 'face_boundaries' => $face_boundary ];
										
									}
									
								}
								
							}
							
						}
						
						// Get all musician links for all images
						$sql_musicians = 'SELECT * FROM images_musicians WHERE images_musicians.image_id IN ('.implode(',', $image_ids).')';
						$stmt_musicians = $this->pdo->prepare($sql_musicians);
						$stmt_musicians->execute();
						$rslt_musicians = $stmt_musicians->fetchAll();
						
						// Flip array of image ids so we can easily put musicians back on original image
						$image_ids = array_flip($image_ids);
						
						if( is_array($rslt_musicians) && !empty($rslt_musicians) ) {
							foreach($rslt_musicians as $musician) {
								
								$image_key = $image_ids[ $musician['image_id'] ];
								
								// If musician has face boundary specified, then we'll save it separately; otherwise just chuck id into one string
								if( strlen($musician['face_boundaries']) ) {
									$images[ $image_key ]['musicians'][ $musician['face_boundaries'] ] = $musician;
								}
								else {
									$images[ $image_key ]['musician_ids'] = strlen( $images[ $image_key ]['musician_ids'] ) ? $images[ $image_key ]['musician_ids'].','.$musician['musician_id'] : $musician['musician_id'];
								}
								
							}
						}
						
						// Loop back through images and clean up musician arrays
						// Also create empty skeleton with detected faces
						for($i=0; $i<$num_images; $i++) {
							
							$images[$i]['musicians'] = is_array($images[$i]['musicians']) ? array_values($images[$i]['musicians']) : [];
							
						}
						
					}
					
					// Get artists etc
					if($args['get'] === 'all') {
						
						// Get user info
						for($i=0; $i<$num_images; $i++) {
							$images[$i]['user'] = $this->access_user->access_user([ 'id' => $images[$i]['user_id'], 'get' => 'name' ]);
						}
						
						foreach(['artists', 'blog', 'labels', 'musicians', 'releases'] as $link_table) {
							$singular_link_table = $link_table === 'blog' ? $link_table : substr($link_table, 0, -1);
							$link_column = $singular_link_table.'_id'.($link_table != 'blog' ? 's' : null);
							
							for($i=0; $i<$num_images; $i++) {
								$links[$link_column] .= ','.$images[$i][$link_column];
							}
							
							$links[$link_column] = explode(',', $links[$link_column]);
							$links[$link_column] = array_unique($links[$link_column]);
							$links[$link_column] = array_filter($links[$link_column], 'is_numeric');
							$links[$link_column] = array_values($links[$link_column]);
							
							$this->access_artist = $this->access_artist ?: new access_artist($this->pdo);
							$this->access_blog = $this->access_blog ?: new access_blog($this->pdo);
							$this->access_label = $this->access_label ?: new access_label($this->pdo);
							$this->access_musician = $this->access_musician ?: new access_musician($this->pdo);
							$this->access_release = $this->access_release ?: new access_release($this->pdo);
							
							if(is_array($links[$link_column]) && !empty($links[$link_column])) {
								$links[$link_table] = $this->{'access_' . $singular_link_table}->{'access_' . $singular_link_table}([ 'ids' => $links[$link_column], 'get' => 'name', 'associative' => true ]);
							}
							
							for($i=0; $i<$num_images; $i++) {
								$image_links = $images[$i][$link_column];
								$image_links = explode(',', $image_links);
								
								foreach($image_links as $image_link_key => $image_link) {
									$image_links[$image_link_key] = $links[$link_table][$image_link];
								}
								
								$images[$i][$link_table] = $image_links;
							}
						}
					}
					
					// Switch to associative
					if($args['associative']) {
						for($i=0; $i<$num_images; $i++) {
							$tmp_images[$images[$i]['id']] = $images[$i];
						}
						
						$images = $tmp_images;
					}
					
					return $images;
				}
				else {
					// Query failure
				}
			}
			else {
				// Mis-match in number of values
			}
		}
	}
?>