<?php
$suppress_echo = true;

include_once("../php/include.php");
include_once("../avatar/class-avatar.php");
include_once("../avatar/avatar-definitions.php");

if($_SESSION["loggedIn"] && is_numeric($_SESSION["userID"])) {
	$sql_check = "SELECT 1 FROM users WHERE id=? AND is_vip=1 LIMIT 1";
	$stmt_check = $pdo->prepare($sql_check);
	$stmt_check->execute([ $_SESSION["userID"] ]);
	$is_vip = $stmt_check->fetchColumn();

	$avatar_options = $avatar_options ?: json_encode($_POST);

	$sql_update = "INSERT INTO users_avatars (user_id, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content=?";
	$stmt_update = $pdo->prepare($sql_update);
	if($stmt_update->execute([ $_SESSION["userID"], $avatar_options, $avatar_options ])) {
		$output["status"] = "success";
		
		// Save avatar as thumbnail
		if(extension_loaded('imagick')) {
			$sql_avatar = "SELECT content FROM users_avatars WHERE user_id=? LIMIT 1";
			$stmt_avatar = $pdo->prepare($sql_avatar);
			$stmt_avatar->execute([ $_SESSION["userID"] ]);
			$rslt_avatar = $stmt_avatar->fetchColumn();
			$rslt_avatar = $rslt_avatar ?: '{"head__base":"default","head__base-color":"i"}';
			
			$avatar = new avatar($avatar_layers, $rslt_avatar, ["is_vip" => true]);
			$av = $avatar->get_avatar_paths(true);
			unset($avatar);
			
			$abba = $avatar_definitions;
			$abba =
				'<?xml version="1.0" encoding="UTF-8" standalone="no" ?>'.
				'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.
				$abba;
			$abba = str_replace('</defs>', '</defs>'.$av, $abba);
			
			$image = new \Imagick();
			try {
				$image->setBackgroundColor(new ImagickPixel('transparent'));
				$image->readImageBlob($abba);
				$image->setImageFormat("PNG");
				$image->resizeImage(200, 200, $image->scaleImage, 0.9, true);
				$image->writeImage('../usericons/avatar-'.$_SESSION["username"].'.png');
			}
			catch (ImagickException $ex) {
				$output["result"][] = $ex->getMessage();
			}
		}
		
		// Award point
		$access_points = new access_points($pdo);
		$access_points->award_points([ 'point_type' => 'edited-avatar', 'allow_multiple' => false ]);
	}
	else {
		$output["result"][] = "Your avatar could not be updated.";
	}
}

$output["result"] = is_array($output["result"]) ? implode("<br />", $output["result"]) : null;
$output["status"] = $output["status"] ?: "error";

echo json_encode($output);