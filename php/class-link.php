<?php

include_once('../php/include.php');

class link {
	
	// Link types
	static public $allowed_link_types = [
		0 => 'other',
		1 => 'official website',
		2 => 'webshop',
		3 => 'blog',
		4 => 'fansite',
		5 => 'SNS',
		6 => 'music',
		7 => 'video',
	];
	
	
	
	// =======================================================
	// Connect
	// =======================================================
	function __construct($pdo) {
		
		// Set up connection
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once('../php/database-connect.php');
		}
		$this->pdo = $pdo;
		
	}
	
	
	
	// =======================================================
	// Clean url
	// =======================================================
	function clean_url( $url ) {
		
		// Make sure has at least one dot
		if( strpos($url, '.') === false ) {
			$url = null;
		}
		
		if( $url ) {
			
			// Remove spaces
			$url = trim($url);
			
			// Make sure has protocol
			$url = preg_replace('/'.'^(?!http)'.'/m', 'http://', $url);
			
			// Remove archive.org
			$url = preg_replace('/'.'^(?:https?:\/\/)?web\.archive\.org\/web\/\d+\/'.'/m', '', $url);
			
		}
		
		return $url;
		
	}
	
	
	
	// =======================================================
	// Make url pretty
	// =======================================================
	static function prettify_url( $url ) {
		
		// Strip beginning
		$url = preg_replace('/'.'^((?:https?:)?(?:\/\/)?(?:www\.)?)'.'/', '', $url);
		
		// Remove trailing slash
		$url = preg_replace( '/'.'(\/)$'.'/', '', $url );
		
		// Twitter
		if( strpos( $url, 'twitter' ) !== false ) {
			$url = str_replace('twitter.com/', '', $url);
			$class = 'symbol__twitter';
		}
		
		// Instagram
		if( strpos( $url, 'instagram' ) !== false ) {
			$url = str_replace('instagram.com/', '', $url);
			$class = 'symbol__instagram';
		}
		
		// YouTube
		if( strpos( $url, 'youtube' ) !== false ) {
			$url = str_replace('youtube.com/channel/', '', $url);
			$short_url = 'YouTube (...'.substr($url, -3).')';
			$class = 'symbol__youtube';
		}
		
		$output = [
			'url' => $url,
			'short_url' => $short_url,
			'class' => $class
		];
		
		return $output;
		
	}
	
	
	
	// =======================================================
	// Guess link type
	// =======================================================
	function guess_link_data( $url, $artist_id ) {
		
		// Possible slugs indiciating that url is of a certain type
		// Order sort of matters--prob want more generic matches last
		$possible_slugs = [
			
			'SNS' => [
				'twitter',
				'facebook',
				'instagram',
				'mixi',
			],
			
			'webshop' => [
				'store',
				'shop',
				'thebase',
				'bandcamp',
			],
			
			'music' => [
				'linkco',
				'spotify',
				'apple',
			],
			
			'video' => [
				'youtu',
			],
			
			'blog' => [
				'ameba',
				'ameblo',
				'line',
				'livedoor',
				'blog',
			],
			
			'fansite' => [
				'grassthread',
				'yunisan',
				'visunavi',
				'vkdb',
				'visulog',
				'wikipedia',
				'okmusic',
				'realsound',
				'jrocknroll',
				'/articles/',
				'/news/',
			],
			
			'official website' => [
				'offi',
				'wix',
				'syncl',
				'home',
				'eonet',
				'fc2web',
				'aremond',
				'pksp',
				'sound.jp',
			],
			
		];
		
		// Make sure URL provided
		if( strlen($url) ) {
			
			// Make url lowercase to help comparisons
			$url = strtolower($url);
			
			// Make sure artist provided
			if( is_numeric($artist_id) ) {
				
				// Get artist friendly for later
				$sql_artist = 'SELECT friendly FROM artists WHERE id=? LIMIT 1';
				$stmt_artist = $this->pdo->prepare($sql_artist);
				$stmt_artist->execute([ $artist_id ]);
				$artist_friendly = $stmt_artist->fetchColumn();
				
				// Push friendly name onto possible website slugs
				$possible_slugs['official website'][] = $artist_friendly;
				$possible_slugs['official website'][] = str_replace('-', '', $artist_friendly);
				
				// Get musicians' names
				$sql_musicians = 'SELECT musicians.id, IF( artists_musicians.as_name IS NOT NULL, COALESCE(artists_musicians.as_romaji, artists_musicians.as_name), musicians.friendly ) AS name FROM artists_musicians LEFT JOIN musicians ON musicians.id=artists_musicians.musician_id WHERE artist_id=?';
				$stmt_musicians = $this->pdo->prepare($sql_musicians);
				$stmt_musicians->execute([ $artist_id ]);
				$rslt_musicians = $stmt_musicians->fetchAll();
				
				// Loop through musicians and clean up name a bit
				if( is_array($rslt_musicians) && !empty($rslt_musicians) ) {
					foreach( $rslt_musicians as $musician ) {
						
						// Make sure we have friendly version
						$name = friendly( $musician['name'] );
						
						// Attempt to handle e.g. Sui-Sui-
						$name = preg_replace('/'.'([a-z]+)-(\1)'.'/', '$1', $name);
						
						// Split name at hyphens in case it's like midorikawa-you, and we'll search for both parts later
						$names = explode('-', $name);
						
						// If name has multiple parts, let's also search by the complete string
						if( count($names) > 1 ) {
							$names[] = $name;
						}
						
						// Put back into new array arranged by id
						$musicians[ $musician['id'] ] = $names;
						
					}
				}
				
				// Loop through groups of slug and if match found, set link type to slug type
				foreach( $possible_slugs as $slug_type => $slugs ) {
					foreach( $slugs as $slug ) {
						if( strpos( $url, $slug ) !== false ) {
							
							$output['type'] = $slug_type;
							break 2;
							
						}
					}
				}
				
				// If we have musicians, and link type is SNS, blog, or official, try to determine if it's for musician or band
				if( is_array($musicians) && !empty($musicians) ) {
					if( $output['type'] === 'blog' || $output['type'] === 'SNS' || $output['type'] === 'official website' ) {
						
						foreach( $musicians as $musician_id => $musician_names ) {
							foreach($musician_names as $musician_name) {
								if( strpos( $url, $musician_name ) !== false ) {
									
									$output['musician_id'] = $musician_id;
									break;
									
								}
							}
						}
						
					}
				}
				
				// Set default type
				$output['type'] = $output['type'] ?: 'other';
				
				// Transform slug type into number
				$output['type'] = array_search( $output['type'], self::$allowed_link_types );
				
			}
			
		}
		
		return $output;

	}
	
	
	
	// =======================================================
	// Add link
	// =======================================================
	function add_link( $url, $artist_id ) {
		
		// Clean url
		$url = $this->clean_url($url);
		
		// Try to guess link type and musician
		$guessed_link_info = $this->guess_link_data( $url, $artist_id );
		
		// Turn link to into array of info
		$link['url'] = $url;
		$link['artist_id'] = $artist_id;
		$link['is_active'] = 1;
		
		// Append guessed info
		if( is_array($guessed_link_info) && !empty($guessed_link_info) ) {
			$link = array_merge( $link, $guessed_link_info );
		}
		
		// Make sure we have an artist id and url
		if( strlen($link['url']) ) {
			
			if( is_numeric($link['artist_id']) ) {
				
				// Check if link already exists
				$sql_check = 'SELECT 1 FROM artists_urls WHERE artist_id=? AND content=? LIMIT 1';
				$stmt_check = $this->pdo->prepare($sql_check);
				$stmt_check->execute([ $link['artist_id'], $link['url'] ]);
				$rslt_check = $stmt_check->fetchColumn();
				
				// If exists, can't move forward
				if( !$rslt_check ) {
					
					// Add the link to the database
					$sql_add = 'INSERT INTO artists_urls (user_id, artist_id, musician_id, content, type, is_active) VALUES (?, ?, ?, ?, ?, ?)';
					$stmt_add = $this->pdo->prepare($sql_add);
					$values_add = [ $_SESSION['user_id'], $link['artist_id'], $link['musician_id'], $link['url'], $link['type'], $link['is_active'] ];
					
					if( $stmt_add->execute($values_add) ) {
						
						$link['id'] = $this->pdo->lastInsertId();
						$output['link'] = $link;
						$output['status'] = 'success';
						
					}
					else {
						$output['result'] = 'Couldn\'t add link.';
					}
					
				}
				else {
					$output['result'] = 'Link already exists.';
				}
				
			}
			else {
				$output['result'] = 'No artist supplied.';
			}
			
		}
		else {
			$output['result'] = 'No url supplied.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		
		return $output;
		
	}
	
	
	
	// =======================================================
	// Update link
	// =======================================================
	function update_link( $link ) {
		
		if( is_array($link) && !empty($link) ) {
			
			// Make sure ID numeric
			if( is_numeric( $link['id'] ) ) {
				
				// Clean vars
				$link['content']     = $this->clean_url($link['content']);
				$link['type']        = is_numeric($link['type']) ? $link['type'] : 0;
				$link['musician_id'] = is_numeric($link['musician_id']) ? $link['musician_id'] : null;
				$link['is_active']   = is_numeric($link['is_active']) ? 1 : 0;
				
				// If we still have a url after cleaning
				if( strlen($link['content']) ) {
					
					// Run query
					$sql_update = 'UPDATE artists_urls SET content=?, type=?, musician_id=?, is_active=? WHERE id=?';
					$stmt_update = $this->pdo->prepare($sql_update);
					$values_update = [
						$link['content'],
						$link['type'],
						$link['musician_id'],
						$link['is_active'],
						$link['id'],
					];
					
					if( $stmt_update->execute( $values_update ) ) {
						$output['status'] = 'success';
					}
					else {
						$output['result'] = 'Couldn\'t update link.';
					}
					
				}
				else {
					$output['result'] = 'URL can\'t be empty. Please ask a moderator to delete link if necessary.';
				}
				
			}
			else {
				$output['result'] = 'No id supplied.';
			}
			
		}
		else {
			$output['result'] = 'No link supplied.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
	
	
	
	// =======================================================
	// Delete link
	// =======================================================
	function delete_link( $link_id ) {
		
		if( is_numeric($link_id) ) {
			
			$sql_delete = 'DELETE FROM artists_urls WHERE id=? LIMIT 1';
			$stmt_delete = $this->pdo->prepare($sql_delete);
			
			if( $stmt_delete->execute([ $link_id ]) ) {
				$output['status'] = 'success';
			}
			else {
				$output['result'] = 'The link couldn\'t be deleted.';
			}
			
		}
		else {
			$output['result'] = 'No link specified.';
		}
		
		$output['status'] = $output['status'] ?: 'error';
		return $output;
		
	}
	
}