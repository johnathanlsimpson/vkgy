<?php

// ========================================================
// Inclusions
// ========================================================

// Get list of artists--song lists are added in JS, depending on selected artist
include_once('../php/function-render_json_list.php');
render_json_list('artist');

// ========================================================
// Page setup
// ========================================================

script([
	'/scripts/external/script-alpine.js',
	'/scripts/external/script-autosize.js',
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-inputmask.js',
	'/scripts/script-initSelectize.js',
	'/scripts/script-initDelete.js',
	'/scripts/script-getJsonLists.js',
	'/scripts/script-triggerChange.js',
	'/songs/script-page-edit.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/songs/style-page-edit.css',
]);

$page_title = 'Edit: '.($song['romaji'] ? $song['romaji'].' ('.$song['name'].')' : $song['name']);

$artist = $song['artist'];
include('../songs/head.php');

// ========================================================
// Get additional data
// ========================================================

// Get all associated tracks
$sql_tracks = 'SELECT release_id, id, romaji, name FROM releases_tracklists WHERE song_id=?';
$stmt_tracks = $pdo->prepare($sql_tracks);
$stmt_tracks->execute([ $song_id ]);
$song['tracks'] = $stmt_tracks->fetchAll(PDO::FETCH_GROUP);

// Previous song
$sql_prev = 'SELECT * FROM songs WHERE artist_id=? AND ( friendly<? OR ( friendly=? AND id<? ) ) ORDER BY friendly DESC LIMIT 1';
$stmt_prev = $pdo->prepare($sql_prev);
$stmt_prev->execute([ $song['artist']['id'], $song['friendly'], $song['friendly'], $song['id'] ]);
$rslt_prev = $stmt_prev->fetch();

if( $rslt_prev ) {
	subnav([
		[
			'text' => $rslt_prev['romaji'] ? lang( $rslt_prev['romaji'], $rslt_prev['name'], 'hidden' ) : $rslt_prev['name'],
			'url' => '/songs/'.$song['artist']['friendly'].'/'.$rslt_prev['id'].'/'.$rslt_prev['friendly'].'/edit/',
			'position' => 'left',
		],
	], 'directional');
}

// Next song
$sql_next = 'SELECT * FROM songs WHERE artist_id=? AND ( friendly>? OR ( friendly=? AND id>? ) ) ORDER BY friendly ASC LIMIT 1';
$stmt_next = $pdo->prepare($sql_next);
$stmt_next->execute([ $song['artist']['id'], $song['friendly'], $song['friendly'], $song['id'] ]);
$rslt_next = $stmt_next->fetch();

if( $rslt_next ) {
	subnav([
		[
			'text' => $rslt_next['romaji'] ? lang( $rslt_next['romaji'], $rslt_next['name'], 'hidden' ) : $rslt_next['name'],
			'url' => '/songs/'.$song['artist']['friendly'].'/'.$rslt_next['id'].'/'.$rslt_next['friendly'].'/edit/',
			'position' => 'right',
		],
	], 'directional');
}

?>

<form action="/songs/function-edit.php" class="col c1" enctype="multipart/form-data" method="post" name="edit-song">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<h1 class="song__title">
		
		<a class="a--inherit symbol__artist" href="<?= '/songs/'.$song['artist']['friendly'].'/'; ?>"><?= $song['artist']['romaji'] ? lang($song['artist']['romaji'], $song['artist']['name'], 'parentheses') : $song['artist']['name']; ?></a>
		
		<div class="any--weaken">
			<a class="a--inherit symbol__song" href="<?= $song['url']; ?>"><?= $song['romaji'] ? lang($song['romaji'], $song['name'], 'parentheses') : $song['name']; ?></a>
			<?= $song['hint'] ? '<span class="any__note" style="font-size:1rem;color:hsl(var(--text));vertical-align:middle;">'.$song['hint'].'</span>' : null; ?>
		</div>
		
	</h1>
	
	<!-- Main stuff -->
	<ul class="text">
		
		<!-- Artist -->
		<li class="input__row">
			
			<!-- Artist -->
			<div class="input__group any--flex-grow">
				<label class="input__label"><?= lang('Artist', 'アーティスト', 'hidden'); ?></label>
				<select class="input any--flex-grow" data-source="artists" name="artist_id" placeholder="artist">
					<option value="" selected></option>
					<?= is_numeric($song['artist_id']) ? '<option value="'.$song['artist_id'].'" selected></option>' : null; ?>
				</select>
			</div>
			
		</li>
		
		<!-- Name and parent -->
		<li class="input__row">
			
			<!-- ID -->
			<div class="input__group any--weaken-color">
				<label class="input__label">ID</label>
				<input data-get="id" data-get-into="value" name="id" placeholder="id" size="2" value="<?= $song['id']; ?>" readonly />
			</div>
			
			<!-- Name -->
			<div class="input__group any--flex-grow">
				<label class="input__label"><?= lang('Song name', '曲の名', 'hidden'); ?></label>
				<input class="input" name="name" placeholder="song name" value="<?= $song['name']; ?>" />
				<input class="input--secondary" name="romaji" placeholder="(romaji)" value="<?= $song['romaji']; ?>" />
				<input name="original_name" value="<?= $song['name']; ?>" type="hidden" />
			</div>
			
			<!-- Friendly -->
			<div class="input__group">
				<label class="input__label"><?= lang('Friendly', 'スラッグ', 'hidden'); ?></label>
				<input class="input" name="friendly" placeholder="friendly" value="<?= $song['friendly']; ?>" />
			</div>
			
		</li>
		
	</ul>
	
	<h3>
		<?= lang('Details', '詳細', 'div'); ?>
	</h3>
	
	<!-- Details -->
	<ul class="text" x-data="{
																										songType:<?= strlen($song['hint']) ? array_search('variant', song::$song_types) : $song['type']; ?>,
																										coverArtistID:null,
																										showEditCover:<?= is_numeric($song['cover_of']) ? 0 : 1; ?>,
																										}">
		
		<!-- Song type -->
		<li class="input__row">
			<div class="input__group">
				
				<label class="input__label">song type</label>
				
				<?php foreach( song::$song_types as $type_key => $type_name ): ?>
					<label class="input__radio">
						<input class="input__choice" name="type" type="radio" value="<?= $type_key; ?>" x-model="songType" <?= $type_key == $song['type'] ? 'checked' : null; ?> />
						<span class="symbol__unchecked"><?= $type_name; ?></span>
					</label>
				<?php endforeach; ?>
				
			</div>
		</li>
		
		<!-- Variant -->
		<li class="input__row" x-show="songType==<?= array_search('variant', song::$song_types); ?>">
			
			<!-- Original song -->
			<div class="input__group any--flex-grow">
				
				<label class="input__label">Original song</label>
				<select class="input any--flex-grow" data-based-on="artist_id" data-populate-on-click="true" data-source="songs" name="variant_of" placeholder="original song" x-ref="foo">
					<option>(select song)</option>
					<?= is_numeric($song['variant_of']) ? '<option value="'.$song['variant_of'].'" selected>'.($song['original']['romaji'] ? $song['original']['romaji'].' ('.$song['original']['name'].')' : $song['original']['name']).'</option>' : null; ?>
				</select>
			
			</div>
			
			<!-- Variant type -->
			<div class="input__group">
				
				<label class="input__label">Variant type</label>
				
				<?php foreach( song::$variant_types as $type_key => $type_name ): ?>
					<label class="input__radio">
						<input class="input__choice" name="variant_type" type="radio" value="<?= $type_key; ?>" <?= $type_key == $song['variant_type'] ? 'checked' : null; ?> />
						<span class="symbol__unchecked"><?= $type_name; ?></span>
					</label>
				<?php endforeach; ?>
				
			</div>
			
			<!-- Variant hint -->
			<div class="input__group" style="width:100%;">
				
				<label class="input__label">Hint</label>
				<input name="hint" placeholder="hint" value="<?= $song['hint']; ?>" x-bind="$refs.foo.value" />
				
				<div class="input__note symbol__info any--weaken-color">
					Add a hint if the variant's name is exactly the same as the original song (e.g. &ldquo;rerecording&rdquo; or &ldquo;2010&rdquo;). The hint will only be shown in dropdowns, etc.
				</div>
				
			</div>
			
		</li>
		
		<!-- Cover -->
		<li class="input__row" x-show="songType==<?= array_search('cover', song::$song_types); ?>">
			
			<div class="input__group any--weaken-color" x-show="!showEditCover">
				
				<label class="input__label">Original</label>
				<div class="input__text">
					<a href="<?= $song['original']['url']; ?>"><?= $song['original']['romaji'] ? $song['original']['romaji'].' ('.$song['original']['name'].')' : $song['original']['name']; ?></a>
					<a class="symbol__edit a--inherit" href="#" @click.prevent="showEditCover=1">edit</a>
				</div>
				
			</div>
			
			<!-- Original artist -->
			<div class="input__group any--flex-grow" x-show="showEditCover">
				
				<label class="input__label">Original artist</label>
				<select class="input any--flex-grow" data-populate-on-click="true" data-source="artists" name="cover_artist_id" placeholder="original artist" x-model="coverArtistID">
					<option value="" selected></option>
				</select>
				
			</div>
			
			<!-- Original song -->
			<div class="input__group any--flex-grow" x-show="coverArtistID!=null">
				
				<label class="input__label">Original song</label>
				<select class="input any--flex-grow" data-based-on="cover_artist_id" data-populate-on-click="true" data-source="songs" name="cover_of" placeholder="original song">
					<option value=""></option>
					<?= is_numeric($song['cover_of']) ? '<option value="'.$song['cover_of'].'" selected>(already set)</option>' : null; ?>
				</select>
				
			</div>
			
		</li>
		
		<!-- Length and date -->
		<li class="input__row">
			
			<!-- Length -->
			<div class="input__group">
				<label class="input__label">Length</label>
				<input name="length" placeholder="01:23" size="4" value="<?= $song['length']; ?>" />
			</div>
			
			<!-- Date -->
			<div class="input__group">
				<label class="input__label">Date appeared</label>
				<input class="input" data-inputmask="'alias': '9999[-99][-99]'" maxlength="10" name="date_occurred" placeholder="yyyy-mm-dd" size="10" value="<?= $song['date_occurred']; ?>" />
			</div>
			
		</li>
		
		<!-- Notes -->
		<li class="input__row">
			<div class="input__group any--flex-grow">
				
				<label class="input__label"><?= lang('Notes', 'ノート', 'hidden'); ?></label>
				<textarea class="input__textarea autosize any--flex-grow" name="notes" placeholder="notes about the song"><?= $song['notes']; ?></textarea>
				
			</div>
		</li>
		
	</ul>
	
	<!-- Advanced -->
	<?php if( $_SESSION['can_approve_data'] ): ?>
		<h3>
			<?= lang('Advanced options', '他のオプション', 'div'); ?>
		</h3>
		
		<ul class="text text--outlined" x-data="{
																																										showFlat:0,
																																										problem:0,
																																										showTracks:<?= count($song['tracks']) ? 0 : 1; ?>
																																										}">
			
			<!-- Problems -->
			<li class="input__row">
				<div class="input__group">
					
					<label class="input__label">This song is...</label>
					
					<label class="input__radio">
						<input class="input__choice" name="problem" type="radio" value="0" x-model="problem" checked />
						<span class="symbol__unchecked">correct</span>
					</label>
					
					<label class="input__radio">
						<input class="input__choice" name="problem" type="radio" value="1" x-model="problem" />
						<span class="symbol__unchecked">a misspelling or duplicate</span>
					</label>
					
					<label class="input__radio">
						<input class="input__choice" name="problem" type="radio" value="2" x-model="problem" />
						<span class="symbol__unchecked">not a song</span>
					</label>
					
				</div>
			</li>
			
			<!-- Duplicate -->
			<li class="input__row" x-show="problem==1">
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Correct song</label>
					<select class="input any--flex-grow" data-populate-on-click="true" data-source="songs" data-based-on="artist_id" name="correct_song_id" placeholder="correct song">
						<option value="" selected></option>
					</select>
					
					<div class="input__note symbol__error any--weaken-color">Any tracks associated with this song will be changed to match the correct song.</div>
					
				</div>
			</li>
			
			<!-- Not a song -->
			<li class="input__row" x-show="problem==2">
				<div class="input__group">
					
					<label class="input__label">Convert to note</label>
					
					<label class="input__radio">
						<input class="input__choice" name="convert_tracks_to_notes" type="radio" value="1" checked />
						<span class="symbol__unchecked">yes</span>
					</label>
					
					<label class="input__radio">
						<input class="input__choice" name="convert_tracks_to_notes" type="radio" value="0" />
						<span class="symbol__unchecked">no</span>
					</label>
					
				</div>
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Change track name</label>
					
					<input placeholder="new name" name="convert_tracks_name" value="<?= $song['name']; ?>" />
					<input class="input--secondary" placeholder="(romaji)" name="convert_tracks_romaji" value="<?= $song['romaji']; ?>" />
					
					
				</div>
				
				<div class="input__note symbol__error any--weaken-color">This song will be deleted and unlinked from any associated tracks.</div>
				<div class="input__note symbol__info any--weaken-color">Optionally, the track names will be changed to comments (e.g. &ldquo;comment&rdquo; will be converted to <span class="any__note">(comment)</span>).</div>
				<div class="input__note symbol__info any--weaken-color">Optionally, you can change the name/romaji of the track.</div>
				
			</li>
			
			<!-- Flat name -->
			<li class="input__row">
				<div class="input__group any--flex-grow">
					
					<label class="input__label">Flat name</label>
					<input class="any--flex-grow" name="flat" placeholder="flat name" value="<?= $song['flat']; ?>" x-show="showFlat" />
					
					<span class="any__note" style="margin-bottom:0.25rem;" x-show="!showFlat"><?= $song['flat']; ?></span>
					<a class="symbol__edit" href="#" style="margin-left:0.5rem;margin-bottom:0.25rem;" @click.prevent="showFlat=1" x-show="!showFlat">edit</a>
					
					<div class="input__note any--weaken-color symbol__info" x-show="showFlat">
						The flat name may contain only kanji, kana, letters, and numbers. It is used to find tracks which are slightly misspelled.
					</div>
					
					<div class="input__note any--weaken-color symbol__error" x-show="showFlat">
						Be careful&mdash;changing the flat name will affect which tracks are automatically linked to the song in the future.
					</div>
					
				</div>
			</li>
			
			<!-- Show linked tracks -->
			<li style="padding-bottom:0;" x-show="!showTracks">
				<a class="symbol__plus" href="#" @click.prevent="showTracks=1">show associated tracks</a>
			</li>
			
			<!-- Linked tracks -->
			<li x-show="showTracks">
				
				<label class="input__label" style="height:1rem;">
					Associated tracks
				</label>
				
				<?php if( is_array($song['tracks']) && !empty($song['tracks']) ): ?>
					
					<ul class="ul--bulleted" style="margin-top:0.5rem;">
						<?php foreach( $song['tracks'] as $release_id => $tracks ): ?>
							<?php foreach( $tracks as $track ): ?>
								<li>
									
									<span class="any__note" style="float:right;">#<?= $track['id']; ?></span>
									<span><?= $track['romaji'] ? lang($track['romaji'], $track['name'], 'div') : $track['name']; ?></span>
									
									<div class="any--weaken">
										<a class="a--inherit" href="<?= '/releases/'.$song['artist']['friendly'].'/'.$song['releases'][ $release_id ]['id'].'/'.$song['releases'][ $release_id ]['friendly'].'/'; ?>">
											<?= $song['releases'][ $release_id ]['quick_name']; ?>
										</a>
									</div>
									
								</li>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
					
				<?php else: ?>
					<span class="symbol__error">This song isn't linked to any tracks.</span>
				<?php endif; ?>
				
			</li>
			
		</ul>
	<?php endif; ?>
	
	<h3>
		<?= lang('Releases', 'リリース', 'div'); ?>
	</h3>
	
	<!-- Releases -->
	<ul class="text text--outlined">
		
		<?php if( is_array($song['releases']) && !empty($song['releases']) ): ?>
			
			<?php foreach( $song['releases'] as $release ): ?>
				<li>
					<a class="symbol__release" href="<?= '/releases/'.$song['artist']['friendly'].'/'.$release['id'].'/'.$release['friendly'].'/'; ?>">
						<?= $release['quick_name'] != $release['name'] ? lang($release['quick_name'], $release['name'], 'parentheses') : $release['name']; ?>
					</a>
				</li>
			<?php endforeach; ?>
			
		<?php else: ?>
			<span class="symbol__error">This song doesn't appear on any releases.</span>
		<?php endif; ?>
		
	</ul>
	
	<!-- Submit -->
	<div class="text text--docked">
		<div class="input__row">
			<div class="input__group any--flex-grow">
				<button class="any--flex-grow" name="submit" type="submit">
					<?= lang('Edit song', '曲を追加', 'hidden'); ?>
				</button>
				<?= '<a class="symbol__song input__text" href="'.$song['url'].'" style="margin-left:0.5rem;">'.( $song['romaji'] ? lang($song['romaji'], $song['name'], 'hidden') : $song['name'] ).'</a>'; ?>
				<span data-role="status"></span>
			</div>
		</div>
		<div class="any--hidden text text--outlined text--notice add__result" data-role="result"></div>
	</div>
	
</form>