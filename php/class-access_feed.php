<?php

	include('../php/include.php');



	class access_feed {

		var $pdo;



		function __construct($pdo) {

			if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {

				include_once("../php/database-connect.php");

				

				$this->pdo = $pdo;

			}

			else {

				$this->pdo = $pdo;

			}

		}



		function print_feed_type($input_string) {

			$feed_types = [

				"release-coverart"     => "Cover art",

				"release-added"        => "Release added",

				"release-edited"       => "Release edited",

				"release-rated"        => "Release rated",

				"release-comment"      => "New comment",

				"release-collected"    => "Release collected",

				"blog-updated"         => "News",

				"blog-commentadded"    => "New comment",

				"song-added"           => "Song added",

				"song-edited"          => "Song edited",

				"song-sampleadded"     => "Sample uploaded",

				"vip-added"            => "VIP upload",

				"vip-commentadded"     => "VIP comment",

				"vip-memberadded"      => "VIP member",

				"artist-added"         => "Artist added",

				"artist-lineupupdated" => "Artist updated",

				"artist-bioupdated"    => "Biography updated",

				"artist-imageadded"    => "Gallery updated",

				"user-added"           => "New member"

			];

			return $feed_types[$input_string];

		}



		function access_feed($arguments = []) {

			$arguments["type"]  = $arguments["type"]  ?: "site";

			$arguments["get"]   = $arguments["get"]   ?: "all";

			$arguments["limit"] = $arguments["limit"] ?: "15";



			if($arguments["type"] === "artist" && is_numeric($arguments["id"])) {

				if($arguments["get"] === "bio_events") {

					$sql = "SELECT type, NULL AS url, event AS text, date_occurred FROM artists_bio WHERE artist_id=? ORDER BY date_occurred ASC, type ASC";

					$sql_args = [$arguments["id"]];

				}

				elseif($arguments["get"] === "all" && !empty($arguments["friendly"])) {

					$sql = "(SELECT 'bio_event' AS type, NULL AS url, event AS text, date_occurred FROM artists_bio WHERE artist_id=?) UNION (SELECT 'release' AS type, CONCAT('/releases/', ?, '/', id, '/', friendly, '/') AS url, CONCAT_WS(' ', COALESCE(romaji, name), COALESCE(press_romaji, press_name), COALESCE(type_romaji, type_name)) AS text, date_occurred FROM releases WHERE artist_id=?) UNION (SELECT 'live' AS type, CONCAT('/lives/', lives.id, '/') AS url, CONCAT(livehouses.area_romaji, ' ', IF(livehouses.romaji IS NOT NULL, livehouses.romaji, livehouses.name)) AS text, lives.date_occurred FROM lives LEFT JOIN livehouses ON lives.livehouse_id = livehouses.id WHERE lineup LIKE CONCAT('%(', ?, ')%')) ORDER BY date_occurred ASC";

					$sql_args = [$arguments["id"], $arguments["friendly"], $arguments["id"], $arguments["id"]];

				}

			}

			elseif($arguments["type"] === "site") {

				if($arguments["get"] === "all") {

					$sql = "SELECT feed.*, users.username FROM feed LEFT JOIN users ON feed.user_id = users.id ORDER BY feed.date_added DESC LIMIT ?";

					$sql_args = [$arguments["limit"]];

				}

				elseif($arguments["get"] === "grouped") {

					$sql  = "SELECT feed.*, users.username, COUNT(feed.id) AS group_count, CONCAT(SUBSTRING(feed.date_added, 1, 10), feed.type, feed.user_id) group_id FROM feed LEFT JOIN users ON feed.user_id = users.id GROUP BY group_id ORDER BY feed.date_added DESC LIMIT ?";

					$sql_args = [$arguments["limit"]];

				}

			}



			if(!empty($sql) && !empty($sql_args)) {

				$stmt = $this->pdo->prepare($sql);

				$stmt->execute($sql_args);

				$tmp_output = $stmt->fetchAll();



				if(!empty($tmp_output) && is_array($tmp_output)) {

					if($arguments["type"] === "site") {

						foreach($tmp_output as $key => $item) {

							$tmp_output[$key]["type"] = $this->print_feed_type($item["type"]);

						}

						if($arguments["get"] === "grouped") {

							foreach($tmp_output as $item) {

								$date_added_key = substr($item["date_added"], 0, 10);

								$output[$date_added_key][] = $item;

							}

						}

						else {

							$output = $tmp_output;

						}

					}

					elseif($arguments["type"] === "artist" && $arguments["get"] === "all") {

						foreach($tmp_output as $key => $item) {

							$y = substr($item["date_occurred"], 0, 4);

							$m = substr($item["date_occurred"], 5, 2);

							$d = substr($item["date_occurred"], 8, 2);

							$output[$y][$m][$d][] = $item;

						}

					}

					else {

						$output = $tmp_output;

					}

				}

			}



			if(!empty($output)) {

				return $output;

			}

		}

	}



		/*

		function get_feed($arguments = []) {

			if($arguments["group"] === true) {

				$sql_get_feed  = "SELECT feed.*, users.username, COUNT(feed.id) AS group_count, CONCAT(SUBSTRING(feed.date_added, 1, 10), feed.type, feed.user_id) group_id FROM feed LEFT JOIN users ON feed.user_id = users.id GROUP BY group_id ORDER BY feed.date_added DESC LIMIT ?";

			}

			else {

				$sql_get_feed  = "SELECT feed.*, users.username FROM feed LEFT JOIN users ON feed.user_id = users.id ORDER BY feed.date_added DESC LIMIT ?";

			}



			if(!is_numeric($arguments["limit"])) {

				$arguments["limit"] = 15;

			}



			$stmt = $this->pdo->prepare($sql_get_feed);

			$stmt->execute([$arguments["limit"]]);

			$tmp_output = $stmt->fetchAll();



			foreach($tmp_output as $key => $item) {

				$tmp_output[$key]["type"] = $this->get_feed_type($item["type"]);

			}



			if($arguments["group"] === true) {

				foreach($tmp_output as $item) {

					$date_added_key = substr($item["date_added"], 0, 10);

					$output[$date_added_key][] = $item;

				}

			}

			else {

				$output = $tmp_output;

			}



			return $output;

		}*/



		/*function access_feed($arguments = []) {

			if(!empty($arguments["artist"])) {

				$artist_friendly = $arguments["artist"];





				$sql_get_artist = "SELECT id FROM artists WHERE friendly=? LIMIT 1";

				$stmt = $this->pdo->prepare($sql_get_artist);

				$stmt->execute([$artist_friendly]);

				while($row = $stmt->fetchColumn()) {

					$artist_id = $row;

				}



				$sql_get_events = "

					(SELECT 'bio_event' AS type, NULL AS url, event AS text, date_occurred FROM artists_bio WHERE artist_id=?)

					UNION

					(SELECT 'release' AS type, CONCAT('/releases/', ?, '/', id, '/', friendly, '/') AS url, CONCAT_WS(' ', COALESCE(romaji, name), COALESCE(press_romaji, press_name), COALESCE(type_romaji, type_name)) AS text, date_occurred FROM releases WHERE artist_id=?)

					UNION

					(SELECT 'live' AS type, CONCAT('/lives/', lives.id, '/') AS url, CONCAT(livehouses.area_romaji, ' ', IF(livehouses.romaji IS NOT NULL, livehouses.romaji, livehouses.name)) AS text, lives.date_occurred FROM lives LEFT JOIN livehouses ON lives.livehouse_id = livehouses.id WHERE lineup LIKE CONCAT('%(', ?, ')%'))

					ORDER BY date_occurred ASC

				";

				$stmt = $this->pdo->prepare($sql_get_events);

				$stmt->execute([$artist_id, $artist_friendly, $artist_id, $artist_id]);

				foreach($stmt->fetchAll() as $row) {

					$y = substr($row["date_occurred"], 0, 4);

					$m = substr($row["date_occurred"], 5, 2);

					$d = substr($row["date_occurred"], 8, 2);

					$events[$y][$m][$d][] = $row;

				}



				return $events;

			}

		}*/











	/*function feed($type = NULL, $title = NULL, $user = NULL, $options = array()) {

		$defaults = array(

			"artistid" => NULL,

			"description" => NULL,

			"url" => "http://weloveucp.com/",

			"image" => "http://weloveucp.com/uploads/butterfly.png",

			"tweet" => NULL,

			"linktitle" => "continue reading",

			"dateadded" => date("Y-m-d H:i:s")

		);

		$options = array_merge($defaults, $options);



		if(!empty($type) && !empty($title) && is_numeric($user)) {

			$allowedTypes = array(

				"release-coverart" => "Cover art added",

				"release-added" => "Release added",

				"release-edited" => "Release info edited",

				"release-rated" => "User rating added",

				"release-collected" => "Release collected by user",

				"release-comment" => "New comment on release",

				"blog-updated" => "News",

				"blog-commentadded" => "New comment",

				"song-added" => "Song info added",

				"song-edited" => "Song info edited",

				"song-sampleadded" => "Sample uploaded",

				"vip-added" => "New VIP upload",

				"vip-commentadded" => "New VIP comment",

				"vip-memberadded" => "New VIP member",

				"artist-added" => "New artist added to database",

				"artist-lineupupdated" => "Artist info updated",

				"artist-bioupdated" => "Artist biography updated",

				"artist-imageadded" => "Images updated",

				"user-added" => "New member",

				"live-added" => "Live added"

			);



			if(array_key_exists($type, $allowedTypes)) {

				$title = sanitize($title);

				$type = sanitize($type);

				$user = sanitize($user);



				$sqlCheck = (strpos($type, "release-") === 0 ? "SELECT * FROM feed WHERE title = '{$title}' AND addedby = '{$user}' AND type LIKE '%release-%'" : "SELECT * FROM feed WHERE title = '{$title}' AND type = '{$type}' AND addedby = '{$user}'");

				$qCheck = mysqli_query($GLOBALS["mysqli"], $sqlCheck) or die(mysqli_error($GLOBALS["mysqli"]));

				if(mysqli_num_rows($qCheck) > 0) {

					while($rCheck = mysqli_fetch_array($qCheck)) {

						$datelimit = strtotime(date("Y-m-d H:i:s")) - (60 * 60 * 24);

						if(strtotime($rCheck["dateadded"]) <= $datelimit) {

							$unique = 1;

						}

					}

				}

				else {

					$unique = 1;

				}



				if(strpos($type, "release-") === 0) {

				}



				if($unique) {

					if(empty($options["tweet"])) {

						$tweetType = explode("-", $type);

						$tweetType = strtoupper($tweetType[0]);



						$countType = strlen($tweetType) + 2 + 1;

						$countTitle = strlen($title) + 1;

						$countURL = 22;



						$allowedLength = 140 - $countType - $countURL;



						if($countTitle <= $allowedLength) {

							$tweetTitle = $title;

						}

						else {

							$tweetTitle = substr($title, 0, ($allowedLength - 1))."…";

						}



						$options["tweet"] = "【{$tweetType}】 {$tweetTitle} {$options["url"]}";



						$countTweet = strlen($options["tweet"]);

						if($countTweet <= (140 - 22 - 1)) {

							$options["tweet"] .= " {$options["image"]}";

						}

					}



					$sql["title"] = $title;

					$sql["type"] = $type;

					$sql["addedby"] = $user;

					$sql["dateadded"] = sanitize($options["dateadded"]);

					$sql["tweet"] = mysqli_real_escape_string($GLOBALS["mysqli"], $options["tweet"]);

					$sql["linktitle"] = sanitize($options["linktitle"]);

					$sql["description"] = sanitize($options["description"], "allowhtml");

					$sql["image"] = sanitize($options["image"], "allowurl");

					$sql["artistid"] = (is_numeric($options["artistid"]) ? sanitize($options["artistid"]) : NULL);

					$sql["url"] = sanitize($options["url"], "allowurl");



					foreach($sql as $key => $value) {

						if($value == NULL) {

							unset($sql[$key]);

						}

					}



					$sqlFeed = "INSERT INTO feed (".implode(", ", array_keys($sql)).") VALUES ('".implode("', '", $sql)."')";

					$qFeed = mysqli_query($GLOBALS["mysqli"], $sqlFeed) or die(mysqli_error($GLOBALS["mysqli"]));

					if(!$qFeed) {

						modal(array("text" => "<strong>Sorry</strong>, the feed() function is malformed: the database could not be updated.", "redirect" => 0, "class" => "errorSymbol"));

					}

				}

			}

		}

		else {

			modal(array("text" => "<strong>Sorry</strong>, the <code>feed()</code> function is malformed: please indicate a type, title, and user ID.", "redirect" => 0, "class" => "errorSymbol"));

		}

	}*/

?>