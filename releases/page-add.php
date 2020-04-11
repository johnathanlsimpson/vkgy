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

	$magazine_id = 8767;
	
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
	if(empty($release['artist']) && strlen($_GET['artist'])) {
		$access_preselected_artist = new access_artist($pdo);
		$release['artist'] = $access_preselected_artist->access_artist([ 'friendly' => sanitize($_GET['artist']), 'get' => 'name' ]);
	}
	
	// If adding magazine, auto set medium and format
	if(!strlen($release['id']) && $release['artist']['friendly'] === 'magazine') {
		$medium_id = 14;
		$format_id = 53;
		$release['medium'][] = 14;
		$release['format'][] = 53;
	}
	
	// If editing magazine, format the magazine-specific fields
	if($release['artist']['friendly'] === 'magazine') {
		
		$release['magazine_name'] = '<option value="'.($release['romaji'] ?: $release['name']).'" selected>'.($release['romaji'] ? $release['romaji'].' ('.$release['name'].')' : $release['name']).'</option>';
		$release['magazine_volume_name'] = $release['press_name'];
		$release['magazine_volume_romaji'] = $release['press_romaji'];
		
		// Unset certain fields so they're not doubled by regular release form
		unset( $release['name'], $release['romaji'], $release['press_name'], $release['press_romaji'] );
		
		// Format tracklist into contents textareas
		if(is_array($release) && is_array($release['tracklist']) && is_array($release['tracklist']['discs']['']['sections']['']['tracks'])) {
			foreach($release['tracklist']['discs']['']['sections']['']['tracks'] as $track_key => $track) {
				
				// Decide which type of feature the artist has, based on the note
				switch($track['notes'][0]['name']) {
					case 'small feature':
						$feature_type = 'magazine_normal';
						break;
					case 'large feature':
						$feature_type = 'magazine_large';
						break;
					case 'cover':
						$feature_type = 'magazine_cover';
						break;
					case 'flyer':
						$feature_type = 'magazine_flyer';
						break;
				}
				
				// Take care of non-DB artists first (their ID is same as magazine, and only have display name)
				if($track['artist']['id'] === $release['artist']['id']) {
					$release[$feature_type] .= "\n".( $track['artist']['display_romaji'] ? $track['artist']['display_romaji'].' ('.$track['artist']['display_name'].')' : $track['artist']['display_name'] )."\n";
				}
				
				// For artists in DB, format as Markdown
				else {
					$release[$feature_type] .=
						'('.$track['artist']['id'].')'.
						'/'.$track['artist']['friendly'].'/'.
						( strlen($track['artist']['display_name']) ? '['.( $track['artist']['display_romaji'] ? $track['artist']['display_romaji'].' ('.$track['artist']['display_name'].')' : $track['artist']['display_name'] ).']' :  null ).
						' ';
				}
				
				// Empty track so it's not populated in hidden part of form
				foreach([ 'notes', 'artist', 'name', 'romaji', 'artist_display_name', 'artist_display_romaji' ] as $track_part) {
					unset($release['tracklist']['discs']['']['sections']['']['tracks'][$track_key][$track_part]);
				}
				
			}
		}
		
		// Clean up double spaces from features
		foreach([ 'normal', 'large', 'cover', 'flyer' ] as $feature_type) {
			$release[ 'magazine_'.$feature_type ] = trim(str_replace("\n\n", "\n", $release[ 'magazine_'.$feature_type ]));
		}
		
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

<style>
	.release--release .magazine--show,
	.release--magazine .magazine--hide {
		display: none;
	}
</style>

<div class="col c1 any--signed-in-only any--margin">
	<?php
		include_once('../php/function-render_json_list.php');
		render_json_list('artist', null, null, null, $release['artist']['id']);
		render_json_list('label');
		render_json_list('song', []);
		
		// Grab possible magazine names
		$sql_magazine_names = 'SELECT name, romaji FROM releases WHERE artist_id=? GROUP BY name ORDER BY friendly ASC';
		$stmt_magazine_names = $pdo->prepare($sql_magazine_names);
		$stmt_magazine_names->execute([ $magazine_id ]);
		$rslt_magazine_names = $stmt_magazine_names->fetchAll();
		
		foreach($rslt_magazine_names as $magazine) {
			$magazine_names[] = [
				$magazine['romaji'] ?: $magazine['name'],
				'',
				$magazine['romaji'] ? $magazine['romaji'].' ('.$magazine['name'].')' : $magazine['name']
			];
		}
		
		echo '<template data-contains="magazines">'.sanitize(json_encode($magazine_names)).'</template>';
	?>

	<form action="" class="<?= $release['artist']['friendly'] === 'magazine' ? 'release--magazine' : 'release--release'; ?>" enctype="multipart/form-data" method="post" name="add">
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
		
		<h2 class="magazine--show">
			<?= lang('Add magazine', '雑誌を追加する', 'div'); ?>
		</h2>
		
		<h2 class="magazine--hide">
			<?= lang((empty($release) ? 'Add' : 'Edit').' release', 'リリースを追加する', 'div'); ?>
		</h2>
		
		<div class="text">
			
			<!-- Magazine: name -->
			<div class="input__row magazine--show">
				<div class="input__group any--flex-grow">
					<label class="input__label">
						<?= lang('Magazine name', '雑誌の名', 'hidden'); ?>
					</label>
					<select class="input any--flex-grow" data-source="magazines" name="magazine_name" placeholder="magazine name">
						<option></option>
						<?= $release['magazine_name']; ?>
					</select>
				</div>
			</div>
			
			<!-- Release: name -->
			<div class="input__row magazine--hide">
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
			
			<!-- Magazine: edition -->
			<div class="input__row magazine--show">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						<?= lang('Volume/edition', '巻・号', 'hidden'); ?>
					</div>
					<input class="input" name="magazine_volume_name" placeholder="volume" value="<?= $release['magazine_volume_name']; ?>" />
					<input class="input--secondary" name="magazine_volume_romaji" placeholder="(romaji)" value="<?= $release['magazine_volume_romaji']; ?>" />
				</div>
			</div>
			
			<!-- Release: press/type -->
			<div class="input__row magazine--hide <?= !$release["press_name"] && !$release["type_name"] ? "any--hidden" : ""; ?> add__press-container">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						<?= lang('Press name', 'プレス', 'hidden'); ?>
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
			
			<!-- Magazine/Release: artist -->
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
				<div class="input__group magazine--hide">
					<button class="symbol__down-caret <?php echo $release["artist"]["display_name"] ? "any--hidden" : ""; ?>" data-show="add__display-name" type="button">
						Display name
					</button>
				</div>
				<div class="input__group magazine--hide any--flex-grow <?php echo !$release["artist"]["display_name"] ? "any--hidden" : "";?> add__display-name">
					<div class="input__label">
						Artist display name
					</div>
					<input class="input" name="artist_display_name" placeholder="artist display name" value="<?php echo $release["artist"]["display_name"]; ?>" />
					<input class="input--secondary" name="artist_display_romaji" placeholder="(romaji)" value="<?php echo $release["artist"]["display_romaji"]; ?>" />
				</div>
			</div>
			
			<!-- Magazine/Release: date/price/UPC -->
			<div class="input__row">
				<div class="input__group">
					<label class="input__label magazine--hide">
						<?= lang('Date', '発売日', 'hidden'); ?>
					</label>
					<label class="input__label magazine--show">
						<?= lang('Date', '発行日', 'hidden'); ?>
					</label>
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
			<?= lang('Details', '詳細', 'div'); ?>
		</h3>
		<div class="text text--outlined">
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Physical medium
					</div>
					<select class="input" id="medium" name="medium[]" placeholder="medium" data-multiple="true" multiple>
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
					<label class="input__select-placeholder" for="medium" tabindex="-1">medium</label>
				</div>
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Format
					</div>
					<select class="input" id="format" name="format[]" placeholder="format" data-multiple="true" multiple>
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
					<label class="input__select-placeholder" for="format" tabindex="-1">format</label>
				</div>
				<div class="input__group magazine--hide">
					<button class="<?php echo $release["format_name"] ? "any--hidden" : ""; ?>" data-show="add__custom-format" type="button">
						Custom format
					</button>
				</div>
			</div>
			
			<div class="input__row magazine--hide <?php echo !$release["format_name"] ? "any--hidden" : ""; ?> add__custom-format">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Custom format
					</div>
					<input class="input" name="format_name" placeholder="eg. super best album" value="<?php echo $release["format_name"]; ?>" />
					<input class="input--secondary" name="format_romaji" placeholder="(romaji)" value="<?php echo $release["format_romaji"]; ?>" />
				</div>
			</div>
			
			<hr class="magazine--hide" />
			
			<!-- Release: venue/limitation -->
			<div class="input__row magazine--hide">
				<div class="input__group any--flex-grow">
					<div class="input__label">
						Venue
					</div>
					<select class="input" data-multiple="true" id="venue" name="venue_limitation[]" placeholder="venue(s)" multiple>
						<?php
							foreach($release_attributes as $attribute) {
								if($attribute['type'] === 'venue_limitation') {
									?>
										<option data-name="<?= $attribute['friendly']; ?>" value="<?= $attribute['id']; ?>" <?= in_array($attribute['id'], $release['venue_limitation']) ? 'selected' : null; ?>><?= ($attribute['romaji'] ?: $attribute['name']).($attribute['romaji'] ? ' ('.$attribute['name'].')' : null); ?></option>
									<?php
								}
							}
						?>
					</select>
					<label class="input__select-placeholder" for="venue" tabindex="-1">venue(s)</label>
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
			
			<!-- Magazine/Release: labels involved -->
			<div class="input__row">
				<?php
					$company_types = [ 'label' => '事務所', 'publisher' => '発売元', 'distributor' => '販売元', 'marketer' => 'マーケター', 'manufacturer' => '製造元', 'organizer' => '企画元'  ];
					
					foreach($company_types as $company_type => $company_type_jp) {
						?>
							<div class="input__group any--flex-grow <?= $company_type != 'label' ? 'magazine--hide' : null; ?> ">
								<?php
									echo '<label class="input__label magazine--hide">'.lang($company_type, $company_type_jp, 'hidden').'</label>';
									echo $company_type === 'label' ? '<label class="input__label magazine--show">'.lang('publisher', '編集・発行', 'hidden').'</label>' : null;
								?>
								<select class="input" data-populate-on-click="true" data-source="labels" id="<?= $company_type; ?>" name="<?php echo $company_type; ?>_id[]" placeholder="<?= $company_type; ?>" multiple data-multiple="true">
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
								<label class="input__select-placeholder" for="<?= $company_type; ?>" tabindex="-1"><?= $company_type; ?></label>
							</div>
						<?php
					}
				?>
			</div>
			<style>
				.input__select-placeholder {
					background: hsl(var(--background--bold));
					background-clip: content-box;
					color: transparent;
					left: 0.5rem;
					line-height: 1.5rem;
					padding: 0.25rem 0 0.25rem 0.5rem;
					pointer-events: none;
					position: absolute;
					right: 0;
				}
				.input__select-placeholder::after {
					border: 5px solid transparent;
					border-top-color: hsl(var(--text--secondary));
					content: "";
					display: block;
					height: 0;
					margin-top: -3px;
					pointer-events: none;
					position: absolute;
					right: 0.5rem;
					top: 50%;
					width: 0;
				}
				select[multiple] + .input__select-placeholder,
				select[data-populate-on-click="true"] + .input__select-placeholder {
					color: hsl(var(--text--secondary));
				}
				.selectize-control.input.single + .input__select-placeholder::after {
					border-color: transparent;
				}
				
				.release--magazine .track__artist.track__artist {
					display: flex;
					flex-grow: 1;
					max-width: none;
					order: -1;
				}
				.release--release .magazine--show:first-of-type + .magazine--hide {
					margin-top: -0.5rem;
				}
			</style>
		</div>
		
		<h3 class="magazine--show">
			<?= lang('Contents', 'コンテンツ', 'div'); ?>
		</h3>
		<div class="text magazine--show">
			
			<div class="symbol__help">
				In each section below, write any artists which have that kind of feature within the magazine. Put a linebreak before each band that isn't in the database.
			</div>
			
			<hr />
			
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<label class="input__label">Normal features (&lt;2 pages)</label>
					<textarea class="input input__textarea any--tributable autosize" name="magazine_normal" placeholder="bands appearing"><?= $release['magazine_normal']; ?></textarea>
				</div>
			</div>
			
			<hr />
			
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<label class="input__label">Large features (2+ pages)</label>
					<textarea class="input input__textarea any--tributable autosize" name="magazine_large" placeholder="bands in large features"><?= $release['magazine_large']; ?></textarea>
				</div>
			</div>
			
			<hr />
			
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<label class="input__label"><?= lang('Cover artist', 'カバー', 'hidden'); ?></label>
					<textarea class="input input__textarea any--tributable autosize" name="magazine_cover" placeholder="bands on cover"><?= $release['magazine_cover']; ?></textarea>
				</div>
			</div>
			
			<hr />
			
			<div class="input__row">
				<div class="input__group any--flex-grow">
					<label class="input__label">Flyers</label>
					<textarea class="input input__textarea any--tributable autosize" name="magazine_flyer" placeholder="bands in flyers"><?= $release['magazine_flyer']; ?></textarea>
				</div>
			</div>
			
		</div>
		
		<h3 class="magazine--hide">
			<?= lang('Tracklist', 'トラックリスト', 'div'); ?>
		</h3>
		<div class="text add__tracklist magazine--hide">
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

			<div class="input__row magazine--hide">
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

			<div class="input__row magazine--hide">
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
		
		<div class="text text--docked">
			<div class="any--flex input__row" data-role="submit-container">
				<div class="input__group any--flex-grow">
					<button class="any--flex-grow" data-role="submit" name="submit" type="submit">
						Submit
					</button>
				</div>
				<div class="input__group <?= $_SESSION['can_delete_data'] ? null : 'any--hidden'; ?>">
					<span class="<?= !is_numeric($release["id"]) ? "any--hidden" : ""; ?> input__checkbox-label symbol__trash" data-role="delete"></span>
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