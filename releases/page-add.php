<?php
	breadcrumbs([
		'Releases' => '/releases/',
	]);
	
	subnav([
		'Add release' => '/releases/add/'.(strlen($_GET['artist']) ? friendly($_GET['artist']).'/' : null),
	], 'interact', true);
	
	subnav([
		lang('Release calendar', '新譜一覧', ['secondary_class' => 'any--hidden']) => '/releases/',
		lang('Search', 'サーチ', ['secondary_class' => 'any--hidden']) => '/search/releases/',
	]);
	
	if(is_numeric($_GET['release'])) {
		$page_header = lang('Edit release', 'リリースを編集する', ['container' => 'div']);
	}
	else {
		$page_header = lang('Add release', 'リリースを追加する', ['container' => 'div']);
	}
	
	subnav([
		lang('Add release', 'リリースを追加する', ['secondary_class' => 'any--hidden']) => '/releases/add/',
	]);
	
	$active_page = '/releases/add/';
	
	script([
		"/scripts/external/script-autosize.js",
		"/scripts/external/script-selectize.js",
		"/scripts/external/script-sortable.js",
		"/scripts/external/script-inputmask.js",
		"/scripts/external/script-easyautocomplete.js",
		'/scripts/external/script-tribute.js',

		"/scripts/script-initDelete.js",
		"/scripts/script-initSelectize.js",
		'/scripts/script-initTribute.js',
		"/scripts/script-showElem.js",
		"/scripts/script-initEasyAutocomplete.js",

		"/releases/script-resetTrackNums.js",
		"/releases/script-trackTemplate.js",
		"/releases/script-page-add.js"
	]);

	style([
		"/style/external/style-selectize.css",
		"/style/external/style-easyautocomplete.css",
		'/style/external/style-tribute.css',
		
		"/style/style-selectize.css",
		"/style/style-easyautocomplete.css",
		"/releases/style-page-add.css"
	]);
	
	if(is_numeric($_GET["release"])) {
		include_once("../php/class-access_release.php");
		$access_release = new access_release($pdo);
		
		$release = $access_release->access_release([
			"release_id" => sanitize($_GET["release"]),
			"get" => "all"
		]);
		
		if(!empty($release)) {
			$release["is_omnibus"] = ($release["artist_id"] === "0" ? true : false);
			if(is_array($release["tracklist"])) {
				$release["is_omnibus"] = array_walk_recursive($release["tracklist"], function($value, $key, $release) {
					if($key === "artist_id" && $value !== $release["artist"]["id"]) {
						return true;
					}
				}, $release);
			}
			
			$release["has_artist_display_names"] = false;
			if(is_array($release["tracklist"])) {
				$release["has_artist_display_names"] = array_walk_recursive($release["tracklist"], function($value, $key) {
					if($key === "artist_display_name" && !empty($value)) {
						return true;
					}
				});
			}
			
			if(empty($release["artist"]["display_name"])) {
				$release["data_states"] .= " hide-artist-display-name";
			}
			
			if(empty($release["type_name"]) && empty($release["press_name"])) {
				$release["data_states"] .= " hide-type";
			}
		}
	}

	if($release["artist"]["id"] === 0) {
		$is_omnibus = true;
	}
	else {
		if(is_array($release) && !empty($release)) {
			if(is_array($release["tracklist"]) && is_array($release["tracklist"]["discs"])) {
				foreach($release["tracklist"]["discs"] as $disc) {
					if(is_array($disc) && is_array($disc["sections"])) {
						foreach($disc["sections"] as $section) {
							if(is_array($section) && is_array($section["tracks"])) {
								foreach($section["tracks"] as $track) {
									if(is_numeric($track["artist"]["id"]) && $track["artist"]["id"] !== $release["artist"]["id"]) {
										$is_omnibus = true;
										break;
									}
								}
							}
						}
					}
				}
			}
		}
	}

	if(!empty($release["quick_name"])) {
		breadcrumbs([
			"Releases" => "/releases/",
			$release["artist"]["quick_name"] => "/releases/".$release["artist"]["friendly"]."/",
			$release["quick_name"] => "/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/",
			"Edit" => "/releases/".$release["artist"]["friendly"]."/".$release["id"]."/".$release["friendly"]."/edit/"
		]);
	}
	else {
		breadcrumbs([
			"Releases" => "/releases/",
			"Add release" => "/releases/add/"
		]);
	}
	
	// Get medium/format/venue/limitation options
	$access_release = $access_release ?: new access_release($pdo);
	$release_attributes = $access_release->get_possible_attributes();
	
	// For selected attribute options, just need IDs
	foreach(['medium', 'format', 'venue_limitation', 'press_limitation_name'] as $key) {
		if(is_array($release[$key]) && !empty($release[$key])) {
			foreach($release[$key] as $attribute_key => $attribute) {
				$release[$key][$attribute_key] = $attribute['attribute_id'];
			}
		}
		else {
			$release[$key] = [];
		}
	}
	
	$release['images'] = is_array($release['images']) ? $release['images'] : [];
	$release['images'] = array_values($release['images']);
	
	// If artist isn't set, but is mentioned in URL, get artist's data
	if(strlen($_GET['artist'])) {
		$access_preselected_artist = new access_artist($pdo);
		$release['artist'] = $access_preselected_artist->access_artist([ 'friendly' => sanitize($_GET['artist']), 'get' => 'name' ]);
	}

	$pageTitle = !empty($release["quick_name"]) ? "Edit: ".$release["quick_name"]." - ".$release["artist"]["quick_name"] : "Add release";
?>

<div class="col c1 any--signed-out-only">
	<div>
		<div class="text text--outlined text--error symbol__error">
			Sorry, only <a href="">signed in</a> members may add releases to the database.
		</div>
	</div>
</div>

<div class="col c1 any--signed-in-only any--margin">
	<?php
		include_once('../php/function-render_json_list.php');
		render_json_list('artist', null, null, null, $release['artist']['id']);
		render_json_list('label');
		render_json_list('song', []);
	?>

	<form action="" enctype="multipart/form-data" method="post" name="add">
		<input data-get="id" data-get-into="value" name="id" type="hidden" value="<?php echo $release["id"]; ?>" />
		<?php
			if($release["artist"]) {
				?>
					<h1>
						<a class="a--inherit artist symbol__artist" data-name="<?= $release['artist']['name']; ?>" data-get="artist_url" data-get-into="href" href="/releases/<?php echo $release["artist"]["friendly"]; ?>/">
							<span data-get="artist_quick_name"><?php echo $release["artist"]["quick_name"]; ?></span>
						</a>
						<div class="any--weaken">
							<a class="a--inherit symbol__release" data-get="url" data-get-into="href" href="/releases/<?= $release["artist"]["friendly"].'/'.(is_numeric($release['id']) ? $release["id"].'/'.$release["friendly"].'/' : null); ?>">
								<span data-get="quick_name"><?= strlen($release["quick_name"]) ? $release['quick_name'] : '(new release)'; ?></span>
							</a>
						</div>
					</h1>
				<?php
			}
			
			if(is_array($release["prev_next"]) && !empty($release["prev_next"])) {
				foreach($release["prev_next"] as $link) {
					$link['url'] = explode('&', $link['url']);
					$link['url'] = $link['url'][0].'edit/'.($link['url'][1] ? '&'.$link['url'][1] : null);
					subnav([
						[
							'text' => lang($link['romaji'] ?: $link['name'], $link['name'], 'hidden'),
							'url' => $link['url'],
							'position' => $link['type'] === 'next' ? 'right' : 'left',
						],
					], 'directional');
				}
			}
			
			// If is omnibus (etc) but was reached by clicking prev/next in an artist's disco, show notice
			if(is_numeric($_GET['prev_next_artist']) && $release['artist']['id'] != $_GET['prev_next_artist']) {
				$traversal_artist = $access_artist->access_artist([ 'id' => sanitize($_GET['prev_next_artist']), 'get' => 'name' ]);
				?>
					<div class="col c1">
						<div>
							<div class="text text--outlined text--notice symbol__help">
								<?php
									$traversal_artist_url = '/releases/'.$traversal_artist['friendly'].'/';
									$traversal_reset_url = '/releases/'.$release['artist']['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/edit/';
									echo lang(
										'The &ldquo;previous/next release&rdquo; links above are based <a class="artist" data-name="'.$traversal_artist['name'].'" href="'.$traversal_artist_url.'">'.($traversal_artist['romaji'] ?: $traversal_artist['name']).'</a>\'s discography. <a class="symbol__next" href="'.$traversal_reset_url.'">Reset?</a>',
										'上記の「戻る/進む」リンクは、<a class="artist" data-name="'.$traversal_artist['name'].'" href="'.$traversal_artist_url.'">'.$traversal_artist['name'].'</a>のディスコグラフィーに基づいています。 <a class="symbol__next" href="'.$traversal_reset_url.'">リセットする?</a>',
										'hidden'
									);
								?>
							</div>
						</div>
					</div>
				<?php
			}
		?>
		
		<h2>
			<?php echo !empty($release) ? "Edit" : "Add"; ?> release
		</h2>

		<h3>
			Basics
		</h3>
		<div class="text">
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Release name
					</div>
					<input class="input" name="name" placeholder="release name" value="<?php echo $release["name"]; ?>" />
					<input class="input--secondary" name="romaji" placeholder="(romaji)" value="<?php echo $release["romaji"]; ?>" />
				</div>
				<div class="input__group">
					<button class="symbol__down-caret <?php echo $release["press_name"] || $release["type_name"] ? "any--hidden" : ""; ?>" data-show="add__press-container" type="button">
						Type/Press
					</button>
				</div>
			</div>


			<div class="input__row <?php echo !$release["press_name"] && !$release["type_name"] ? "any--hidden" : ""; ?> add__press-container">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Press name
					</div>
					<input class="input" name="press_name" placeholder="press name" value="<?php echo $release["press_name"]; ?>" />
					<input class="input--secondary" name="press_romaji" placeholder="(romaji)" value="<?php echo $release["press_romaji"]; ?>" />
				</div>

				<div class="input__group any--flex-grow">
					<div class="input__label">
						Type name
					</div>
					<input class="input" name="type_name" placeholder="type name" value="<?php echo $release["type_name"]; ?>" />
					<input class="input--secondary" name="type_romaji" placeholder="(romaji)" value="<?php echo $release["type_romaji"]; ?>" />
				</div>
			</div>


			<div class="input__row">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Artist
					</div>
					<select class="input" name="artist_id" placeholder="choose an artist" data-source="artists">
						<option></option>
						<?php
							if(isset($release["artist"]["id"])) {
								?>
									<option data-name="<?php echo $release["artist"]["quick_name"]; ?>" value="<?php echo $release["artist"]["id"]; ?>" selected>
										<?php
											echo $release["artist"]["quick_name"];
										?>
									</option>
								<?php
							}
						?>
					</select>
				</div>
				<div class="input__group">
					<button class="symbol__down-caret <?php echo $release["artist"]["display_name"] ? "any--hidden" : ""; ?>" data-show="add__display-name" type="button">
						Display name
					</button>
				</div>
				<div class="input__group any--flex-grow <?php echo !$release["artist"]["display_name"] ? "any--hidden" : "";?> add__display-name">
					<div class="input__label">
						Artist display name
					</div>
					<input class="input" name="artist_display_name" placeholder="artist display name" value="<?php echo $release["artist"]["display_name"]; ?>" />
					<input class="input--secondary" name="artist_display_romaji" placeholder="(romaji)" value="<?php echo $release["artist"]["display_romaji"]; ?>" />
				</div>
			</div>


			<div class="input__row">
				<div class="input__group">
					<div class="input__label">
						Date
					</div>
					<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_occurred" placeholder="yyyy-mm-dd" size="10" value="<?php echo $release["date_occurred"] !== "0000-00-00" ? $release["date_occurred"] : null; ?>" />
				</div>
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Price
					</div>
					<input class="input" name="price" placeholder="eg. 1,000 yen" value="<?php echo $release["price"]; ?>" />
				</div>
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Catalog num
					</div>
					<input class="input" name="upc" placeholder="eg. UCCD-001" value="<?php echo $release["upc"]; ?>" />
				</div>
			</div>
		</div>

		<h3>
			Production details
		</h3>
		<div class="text text--outlined">
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Physical medium
					</div>
					<select class="input" name="medium[]" placeholder="medium" data-multiple="true" multiple>
						<option></option>
						<?php
							foreach($release_attributes as $attribute) {
								if($attribute['type'] === 'medium') {
									?>
										<option data-name="<?= $attribute['friendly']; ?>" value="<?= $attribute['id']; ?>" <?= in_array($attribute['id'], $release['medium']) ? 'selected' : null; ?>><?= ($attribute['romaji'] ?: $attribute['name']).($attribute['romaji'] ? ' ('.$attribute['name'].')' : null); ?></option>
									<?php
								}
							}
						?>
					</select>
				</div>
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Format
					</div>
					<select class="input" name="format[]" placeholder="format" data-multiple="true" multiple>
						<option></option>
						<?php
							foreach($release_attributes as $attribute) {
								if($attribute['type'] === 'format') {
									?>
										<option data-name="<?= $attribute['friendly']; ?>" value="<?= $attribute['id']; ?>" <?= in_array($attribute['id'], $release['format']) ? 'selected' : null; ?>><?= ($attribute['romaji'] ?: $attribute['name']).($attribute['romaji'] ? ' ('.$attribute['name'].')' : null); ?></option>
									<?php
								}
							}
						?>
					</select>
				</div>
				<div class="input__group">
					<button class="<?php echo $release["format_name"] ? "any--hidden" : ""; ?>" data-show="add__custom-format" type="button">
						Custom format
					</button>
				</div>
			</div>


			<div class="input__row <?php echo !$release["format_name"] ? "any--hidden" : ""; ?> add__custom-format">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Custom format
					</div>
					<input class="input" name="format_name" placeholder="eg. super best album" value="<?php echo $release["format_name"]; ?>" />
					<input class="input--secondary" name="format_romaji" placeholder="(romaji)" value="<?php echo $release["format_romaji"]; ?>" />
				</div>
			</div>

			<hr />


			<div class="input__row">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Venue
					</div>
					<select class="input" data-multiple="true" name="venue_limitation[]" placeholder="venue(s)" multiple>
						<?php
							//$release['venue_limitation'] = is_array($release['venue_limitation']) ? $release['venue_limitation'] : [];
							
							foreach($release_attributes as $attribute) {
								if($attribute['type'] === 'venue_limitation') {
									?>
										<option data-name="<?= $attribute['friendly']; ?>" value="<?= $attribute['id']; ?>" <?= in_array($attribute['id'], $release['venue_limitation']) ? 'selected' : null; ?>><?= ($attribute['romaji'] ?: $attribute['name']).($attribute['romaji'] ? ' ('.$attribute['name'].')' : null); ?></option>
									<?php
								}
							}
						?>
					</select>
				</div>
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Pressing type
					</div>
					<select class="input" name="press_limitation_name" placeholder="pressing type">
						
						<?php
							foreach($release_attributes as $attribute) {
								if($attribute['type'] === 'press_limitation_name') {
									?>
										<option data-name="<?= $attribute['friendly']; ?>" value="<?= $attribute['id']; ?>" <?= in_array($attribute['id'], $release['press_limitation_name']) ? 'selected' : null; ?>><?= ($attribute['romaji'] ?: $attribute['name']).($attribute['romaji'] ? ' ('.$attribute['name'].')' : null); ?></option>
									<?php
								}
							}
						?>
					</select>
				</div>
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Copies made
					</div>
					<input class="input" name="press_limitation_num" placeholder="eg. 1000" size="6" value="<?php echo $release["press_limitation_num"]; ?>" />
				</div>
			</div>


			<hr />


			<div class="input__row">
				<?php
					foreach(["label", "publisher", "distributor", "marketer", "manufacturer", "organizer"] as $company_type) {
						?>
							<div class="input__group any--flex-grow">
								<div class="input__label">
									<?php
										echo $company_type;
									?>
								</div>
								<select class="input" data-populate-on-click="true" data-source="labels" name="<?php echo $company_type; ?>_id[]" multiple data-multiple="true">
									<?php
										if(is_array($release[$company_type])) {
											foreach($release[$company_type] as $company) {
												?>
													<option data-name="<?php echo $company["quick_name"]; ?>" value="<?php echo $company["id"]; ?>" selected><?php echo $company["quick_name"]; ?></option>
												<?php
											}
										}
										else {
											?>
												<option></option>
											<?php
										}
									?>
								</select>
							</div>
						<?php
					}
				?>
			</div>


		</div>
		
		<h3>
			Tracklist
		</h3>
		<div class="text add__tracklist">
			<?php
				ob_start();
					?>
						<div class="track ?class">
							<div class="input__row track__disc">
								<div class="input__group any--flex-grow">
									<span class="input__label track__disc-label">
										Disc
									</span>
									<input class="input"             value="?disc_name"   name="tracklist[disc_name][]"               placeholder="disc name" />
									<input class="input--secondary"  value="?disc_romaji" name="tracklist[disc_romaji][]"             placeholder="(romaji)" />
								</div>
							</div>
							<div class="input__row track__section">
								<div class="input__group any--flex-grow">
									<span class="input__label">
										Section
									</span>
									<input class="input"             value="?section_name"  name="tracklist[section_name][]"            placeholder="section name" />
									<input class="input--secondary"  value="?section_romaji"  name="tracklist[section_romaji][]"          placeholder="(romaji)" />
								</div>
							</div>
							<div class="input__row track__song-container">
								<div class="input__group track__song any--flex-grow">
									<span class="track__num"></span>
									<input class="input"             value="?name"  name="tracklist[name][]"               placeholder="song name" />
									<input class="input--secondary"  value="?romaji"  name="tracklist[romaji][]"             placeholder="(romaji)" />
								</div>
								<div class="input__group track__artist">
									<label class="input__label">Artist</label>
									<select class="input" data-populate-on-click="true" name="tracklist[artist_id][]"               placeholder="artist(s)"  data-source="artists" data-multiple="true">
										<option value=""></option>
										?artist
									</select>
								</div>
								<div class="input__group track__display-name">
									<label class="input__label">Display artist name as</label>
									<input class="input"             value="?artist_display_name"  name="tracklist[artist_display_name][]"    placeholder="artist name" />
									<input class="input--secondary"  value="?artist_display_romaji"  name="tracklist[artist_display_romaji][]"  placeholder="(romaji)" />
								</div>
								
								<div class="track__song-controls input__group">
									<button class="track__song-control track__reorder" tabindex="-1" type="button">⇅</button>
								</div>
								<div class="track__song-controls input__group">
									<button class="track__song-control" data-add="song" tabindex="-1" type="button">+</button>
								</div>
							</div>
							
							<div class="input__row track__tracklist-controls">
								<div class="input__group">
									<button class="track__control" data-add="disc" type="button">
										Add disc
									</button>
								</div>
								<div class="input__group">
									<button class="track__control" data-add="section" type="button">
										Add section
									</button>
								</div>
								<div class="input__group">
									<button class="track__control" data-add="songs" type="button">
										Add tracks
									</button>
								</div>
								<div class="input__group">
									<button class="track__control" data-show="track--show-artist" type="button">
										Show artists
									</button>
								</div>
								<div class="input__group">
									<button class="track__control" data-show="track--show-artist track--show-display-name" type="button">
										Show artist display names
									</button>
								</div>
							</div>
						</div>
					<?php
				$template = ob_get_clean();

				function print_template($template, $insert_into_template = []) {
					$template_var_pattern = "(\?\w+)";

					echo preg_replace_callback("/".$template_var_pattern."/", function($match) use($insert_into_template) {
						$match = substr(end($match), 1);
						return $insert_into_template[$match];
					}, $template);
				}

				if(is_array($release["tracklist"])) {
					foreach($release["tracklist"]["discs"] as $disc) {
						if(!empty($disc["disc_name"])) {
							print_template($template, [
								"class" => "track--show-disc",
								"disc_name" => $disc["disc_name"],
								"disc_romaji" => $disc["disc_romaji"]
							]);
						}

						foreach($disc["sections"] as $section) {
							if(!empty($section["section_name"])) {
								print_template($template, [
									"class" => "track--show-section",
									"section_name" => $section["section_name"],
									"section_romaji" => $section["section_romaji"]
								]);
							}

							foreach($section["tracks"] as $track) {
								print_template($template, [
									"class" => "track--show-song".($is_omnibus ? " track--show-artist" : ""),
									"name" => str_replace(["&#40;", "&#41;"], ["\&#40;", "\&#41;"], $track["name"]),
									"romaji" => str_replace(["&#40;", "&#41;"], ["\&#40;", "\&#41;"], $track["romaji"]),
									"artist" => '<option value="'.$track["artist"]["id"].'" '.($track["artist"]["i7d"] !== $release["artist"]["id"] ? "selected" : null).'>'.$track["artist"]["quick_name"].'</option>',
									"artist_display_name" => $track["artist"]["display_name"],
									"artist_display_romaji" => $track["artist"]["display_romaji"]
								]);
							}

							print_template($template, [
								"class" => "track--show-controls"
							]);
						}
					}
				}
				else {
					for($i = 0; $i < 5; $i++) {
						print_template($template, [
							"class" => "track--show-song",
							"name" => $track["name"],
							"romaji" => $track["romaji"]
						]);
					}

					print_template($template, [
						"class" => "track--show-controls"
					]);
				}
			?>
			
			<hr />
			
			<div class="symbol__help any--weaken-color" style="margin-bottom: 1rem;">
				Note: for faster entry, you can copy + paste a list of song titles separated by line breaks.
			</div>
			
			<button class="track__control" type="button" data-clear>Clear tracklist</button>
		</div>
		<div class="any--hidden track__template">
			<?php
				print_template($template, [
					"class" => "?class"
				]);
			?>
		</div>

		<div>
			<h3>
				Images
			</h3>
			<?php
				include('../images/function-render_image_section.php');
				render_image_section($release['images'], [
					'item_type'     => 'release',
					'item_id'       => $release['id'],
					'item_name'     => $release['quick_name'],
					'description'   => $release['quick_name'].' cover',
					'id'            => $release['image_id'],
					'hide_selects'  => true,
					'hide_markdown' => true,
					'is_default'    => 'checked',
				]);
			?>
		</div>
		
		<h3>
			Additional
		</h3>
		<div class="text">
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<label class="input__label">
						Friendly URL
					</label>
					<input class="input" name="friendly" value="<?php echo $release["friendly"]; ?>" placeholder="friendly url"/>
				</div>
			</div>

			<div class="input__row">
				<div class="input__group any--flex-grow">
					<span class="input__label">
						Notes
					</span>
					<textarea class="input input__textarea any--tributable autosize" name="notes" placeholder="notes"><?php echo implode("\n---\n", (is_array($release["notes"]) ? $release["notes"] : [])); ?></textarea>
				</div>
			</div>

			<div class="input__row">
				<div class="input__group any--flex-grow">
					<span class="input__label">
						Booklet credits
					</span>
					<textarea class="input input__textarea any--tributable autosize" name="credits" placeholder="booklet credits"><?php
						if(is_array($release["credits"]) && !empty($release["credits"])) {
							foreach($release["credits"] as $key => $credit) {
								echo $credit["title"].($credit["title"] && $credit["credit"] ? " - " : null).$credit["credit"];
								echo $key < (count($release["credits"]) - 1) ? "\n" : null;
							}
						}
					?></textarea>
				</div>
			</div>

			<div class="input__row">
				<div class="input__group any--flex-grow">
					<label class="input__label">
						Concept/tagline
					</label>
					<input class="input" name="concept" placeholder="concept/tagline" value="<?php echo $release["concept"]; ?>" />
					<input class="input input--secondary" name="concept_romaji" placeholder="(romaji)" value="<?php echo $release["concept_romaji"]; ?>" />
				</div>
				<div class="input__group">
					<label class="input__label">
						JAN code
					</label>
					<input class="input" name="jan_code" value="<?php echo $release["jan_code"]; ?>" placeholder="jan code" />
				</div>
			</div>
		</div>
		
		<?php
			if(is_array($release["prev_next"]) && !empty($release["prev_next"])) {
				?>
					<div class="col c2 any--margin">
						<div class="release__prev-next">
							<?php
								foreach($release["prev_next"] as $link) {
									if($link["type"] === "prev") {
										?>
											<h5>
												Previous release
											</h5>
											<a href="<?php echo $link["url"].'edit/'; ?>">
												<span class="symbol__previous"></span>
												<?php echo $link["quick_name"]; ?>
											</a>
										<?php
									}
								}
							?>
						</div>
						<div style="text-align: right;">
							<?php
								foreach($release["prev_next"] as $link) {
									if($link["type"] === "next") {
										?>
											<h5>
												Next release
											</h5>
											<a href="<?php echo $link["url"].'edit/'; ?>">
												<?php echo $link["quick_name"]; ?>
												<span class="symbol__next"></span>
											</a>
										<?php
									}
								}
							?>
						</div>
					</div>
				<?php
			}
		?>
		
		<div class="text text--docked">
			<div class="any--flex input__row" data-role="submit-container">
				<div class="input__group any--flex-grow">
					<button class="any--flex-grow" data-role="submit" name="submit" type="submit">
						Submit
					</button>
				</div>
				<div class="input__group">
					<span class="<?php echo !is_numeric($release["id"]) ? "any--hidden" : ""; ?> input__checkbox-label symbol__trash" data-role="delete"></span>
				</div>
				<span data-role="status"></span>
			</div>
			
			<div class="any--flex any--hidden" data-role="edit-container">
				<a class="any--align-center a--outlined a--padded any--flex-grow symbol__release" data-get="url" data-get-into="href" href="">View release</a>
				<a class="add__edit any--weaken-color a--outlined a--padded symbol__edit" data-get="edit-url" data-get-into="href" data-role="edit" href="">Edit</a>
				<a class="add__edit any--weaken-color a--outlined a--padded symbol__copy" data-role="duplicate" href="/releases/add/">Duplicate</a>
			</div>
			
			<div class="text text--outlined text--error symbol__error add__result" data-role="result"></div>
		</div>
	</form>
</div>

<?php
$documentation_page = 'releases';
include('../documentation/index.php');