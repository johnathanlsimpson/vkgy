<?php
	include_once("../php/include.php");
	
	$access_release = new access_release($pdo);
	
	if($_GET["action"] === "download" && $_SESSION["loggedIn"] && strlen($_GET["username"]) > 0 && $_SESSION["username"] === $_GET["username"]) {
		$collection = $access_release->access_release(["user_id" => $_SESSION["userID"], "get" => "list", "limit" => 2000]);
		$collection = is_array($collection) ? array_values($collection) : [];
		$num_collected = count($collection);
		
		usort($collection, function($a, $b) {
			return $a["artist"]["friendly"].$a["friendly"] <=> $b["artist"]["friendly"].$b["friendly"];
		});
		
		$filename = "vkgy-".$_SESSION["username"]."-".($_GET["limit"] === "selling" ? "selling" : "collection")."-".date("Ymd").".csv";
		
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Type: application/force-download, charset=UTF-8; encoding=UTF-8");
		header("Content-Length: ".strlen($txt_file));
		header("Connection: close");
		
		function output_line($input) {
			if(is_array($input) && !empty($input)) {
				$string = implode("\t", $input)."\r\n";
				$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
				echo $string;
			}
		}
		
		echo output_line([
			"Artist",
			"Artist (romanized)",
			"Title",
			"Title (romanized)",
			"Date",
			"Medium",
			"Price (yen)",
			"Is for sale",
			"Information",
		]);
		
		for($i=0; $i<$num_collected; $i++) {
			if($_GET["limit"] !== "selling" || ($_GET["limit"] === "selling" && $collection[$i]["is_for_sale"])) {
				output_line([
					$collection[$i]["artist"]["name"],
					$collection[$i]["artist"]["romaji"] ?: $collection[$i]["artist"]["name"],
					$collection[$i]["name"],
					$collection[$i]["quick_name"],
					$collection[$i]["date_occurred"],
					$collection[$i]["medium"],
					$collection[$i]["price"],
					$collection[$i]["is_for_sale"],
					"https://vk.gy/releases/".$collection[$i]["artist"]["friendly"]."/".$collection[$i]["id"]."/".$collection[$i]["friendly"]."/",
				]);
			}
			
			unset($collection[$i]);
		}
	}
?>