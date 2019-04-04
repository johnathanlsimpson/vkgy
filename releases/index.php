<?php
	$access_release = new access_release($pdo);
	$access_artist = new access_artist($pdo);
	
	include("../database/head.php");
	include("../releases/head.php");
	
	if(is_numeric($_GET["id"])) {
		$release = $access_release->access_release(["release_id" => $_GET["id"], "get" => "all"]);
		
		if(is_array($release) && !empty($release)) {
			$page_description = $release["artist"]["quick_name"]." 「".$release["quick_name"]."」 release information, reviews, etc. ".$release["artist"]["name"]." 「".$release["name"]." ".$release["press_name"]." ".$release["type_name"]."」のリリース情報、レビュー、など。 | vk.gy (ブイケージ)";
			
			$sql_images = "SELECT * FROM images WHERE is_release=? AND release_id LIKE CONCAT('%(', ?, ')%')";
			$stmt_images = $pdo->prepare($sql_images);
			$stmt_images->execute(['1', $release["id"]]);
			
			$release["images"] = $stmt_images->fetchAll();
			
			include_once("../releases/page-id.php");
		}
		else {
			$error = "Sorry, the requested release could not be found.";
			include_once("../releases/page-index.php");
		}
	}
	
	elseif(!empty($_GET["artist"])) {
		$_GET["artist"] = friendly($_GET["artist"]);
		
		if(!empty($_GET["artist"])) {
			$artist = $access_artist->access_artist([ "friendly" => $_GET["artist"], "get" => "name", "limit" => "1" ]);
			
			if(is_array($artist) && !empty($artist)) {
				$releases = $access_release->access_release([ "artist_id" => $artist["id"], "get" => "basics" ]);
				
				if(is_array($releases) && !empty($releases)) {
					$page_description = $artist["quick_name"]." full discography and release information.\n「".$artist["name"]."」のディスコグラフィ、リリース情報、など。\n| vkgy (ブイケージ)";
					
					breadcrumbs([
						$artist["quick_name"] => "/releases/".$artist["friendly"]."/",
					]);
					
					include_once("../releases/page-artist.php");
				}
				else {
					$error = '<a href="/artists/'.$artist["friendly"].'/">'.$artist["quick_name"].'</a> doesn\'t have any releases in the database yet.';
					include_once("../releases/page-index.php");
				}
			}
			else {
				$error = 'Sorry, <span class="any__note">'.$_GET["artist"]."</span> can't be found in the database.";
				include_once("../releases/page-index.php");
			}
		}
	}
	else {
		include_once("../releases/page-index.php");
	}
?>