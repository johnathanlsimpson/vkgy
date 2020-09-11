<?php
	function sort_musicians($musicians) {
		if(is_array($musicians)) {
			foreach($musicians as $musician) {
				$position = ["unknown", "vocals", "guitar", "bass", "drums", "keys", "other", "staff"][$musician["position"]];
				$position = strpos(strtolower($musician["position_name"]), "support") !== false ? "Support ".$position : $position;
				$position = !empty($musician["position_name"]) ? ($musician["position_romaji"] ? $musician["position_romaji"].' <span class="any--weaken">('.$musician["position_name"].")</span>" : $musician["position_name"]) : $position;
				
				$name = $musician["as_name"] ?: $musician["name"];
				$romaji = $musician["as_name"] ? $musician["as_romaji"] : $musician["romaji"];
				$quick_name = $romaji ?: $name;
				
				$type = (strpos(strtolower($position), "roadie") !== false || $musician['position'] == 7) ? 3 : ($musician["to_end"] ? 1 : 2);
				
				$history = [];
				
				if(is_array($musician["history"])) {
					foreach($musician["history"] as $period_key => $period) {
						if(is_array($period)) {
							foreach($period as $band_key => $band) {
								$band_name = $band["display_name"] ?: $band["name"];
								$band_romaji = $band["display_name"] ? $band["display_romaji"] : $band["romaji"];
								$band_quick_name = $band_romaji ?: $band_name;
								$url = $band["friendly"] ? "/artists/".$band["friendly"]."/" : null;
								
								$notes = [];
								
								if(is_array($band["notes"])) {
									foreach($band["notes"] as $note) {
										$notes[] = "(".$note.")";
									}
								}
								else {
									$notes = null;
								}
								
								$history[$period_key][$band_key] = [
									'id' => $band['id'],
									"name" => $band_name,
									"romaji" => $band_romaji,
									"quick_name" => $band_quick_name,
									"url" => $url,
									"notes" => $notes,
									'is_session' => $band['type'] === 'session',
									'is_hidden' => $band['is_hidden']
								];
							}
						}
					}
				}
				
				$output[$type][] = [
					"id" => $musician["id"],
					"friendly" => $musician["friendly"],
					"name" => $name,
					"romaji" => $romaji,
					"quick_name" => $quick_name,
					"position" => $musician["position"],
					"position_name" => $position,
					"history" => $history
				];
			}
		}
		
		if(is_array($output)) {
			ksort($output);
		}
		return $output;
	}
?>