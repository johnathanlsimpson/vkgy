<?php
	include_once("../php/include.php");
	
	function image_exists($input_string, $pdo) {
		if(substr($input_string, 0, 3) === "../") {
			$input_string = substr($input_string, 2);
		}
		
		if(preg_match("/"."\/images\/(\d+)(?:-[\w-]*)\.(jpg|jpeg|gif|png)"."/", $input_string, $match)) {
			if(is_array($match)) {
				$check_file = strtolower("/images/image_files/".$match[1].".".$match[2]);
			}
		}
		elseif(preg_match("/"."\/artists\/([\w-]+)\/main(?:\.(?:small|medium|large))?.jpg"."/", $input_string, $match)) {
			if(is_array($match)) {
				$access_artist = new access_artist($pdo);
				$artist_id = $access_artist->access_artist(["friendly" => friendly($match[1]), "get" => "id"])["id"];
				
				if(is_numeric($artist_id)) {
					$sql_check_default = "SELECT id, extension FROM images WHERE artist_id = CONCAT('(', ?, ')') AND is_default=? AND is_release IS NULL LIMIT 1";
					$stmt_check_default = $pdo->prepare($sql_check_default);
					$stmt_check_default->execute([$artist_id, 1]);
					$image = $stmt_check_default->fetch();
					
					if(is_array($image) && !empty($image)) {
						$check_file = "/images/image_files/".$image["id"].".".strtolower($image["extension"]);
					}
				}
			}
		}
		else {
			$check_file = $input_string;
		}
		
		if(!empty($check_file)) {
			return file_exists("..".$check_file);
		}
	}
?>