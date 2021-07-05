<?php

// ========================================================
// Inclusions
// ========================================================

include_once('../php/class-parse_markdown.php');
$markdown_parser = new parse_markdown($pdo);

// ========================================================
// Page setup
// ========================================================

style([
	'/songs/style-page-song.css',
]);

$page_title = $song['romaji'] ? $song['romaji'].' ('.$song['name'].')' : $song['name'];

$active_page = '/songs/'.$song['artist']['friendly'].'/';

$artist = $song['artist'];
include('../songs/head.php');

// ========================================================
// Get additional data
// ========================================================

// Get artist's other songs
$songs = $access_song->access_song([ 'artist_id' => $song['artist']['id'], 'get' => 'name' ]);

// Previous song
$sql_prev = 'SELECT * FROM songs WHERE artist_id=? AND ( friendly<? OR ( friendly=? AND id<? ) ) ORDER BY friendly DESC LIMIT 1';
$stmt_prev = $pdo->prepare($sql_prev);
$stmt_prev->execute([ $song['artist']['id'], $song['friendly'], $song['friendly'], $song['id'] ]);
$rslt_prev = $stmt_prev->fetch();

if( $rslt_prev ) {
	subnav([
		[
			'text' => $rslt_prev['romaji'] ? lang( $rslt_prev['romaji'], $rslt_prev['name'], 'hidden' ) : $rslt_prev['name'],
			'url' => '/songs/'.$song['artist']['friendly'].'/'.$rslt_prev['id'].'/'.$rslt_prev['friendly'].'/',
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
			'url' => '/songs/'.$song['artist']['friendly'].'/'.$rslt_next['id'].'/'.$rslt_next['friendly'].'/',
			'position' => 'right',
		],
	], 'directional');
}

// ========================================================
// Transform data
// ========================================================

// Explode notes into list, then parse each one
if( $song['notes'] ) {
	
	$song['notes'] = explode("---", $song['notes']);
	
	foreach( $song['notes'] as $note_key => $note ) {
		$song['notes'][$note_key] = $markdown_parser->parse_markdown($note);
	}
	
}

// Count releases for statistics
$song['num_appearances'] = is_array($song['releases']) ? count($song['releases']) : 0;

?>
	
<?= $error ? '<div class="col c1"><div class="text text--outlined text--error">'.$error.'</div></div>' : null; ?>

<div class="col c3-AAB">
	
	<!-- Song -->
	<div>
		
		<div class="text">
			
			<h2>
				<?= $song['romaji'] ? lang($song['romaji'], $song['name'], 'div') : $song['name']; ?>
				<?= $song['hint'] ? '&nbsp;<span class="any__note" style="font-size:1rem;font-weight:normal;line-height:1;">'.$song['hint'].'</span>' : null; ?>
			</h2>
			
			<div class="data__container">
				
				<div class="data__item">
					<h5>Type</h5>
					<?= song::$song_types[ $song['type'] ]; ?>
				</div>
				
				<?php if( is_numeric($song['variant_type']) ): ?>
					<div class="data__item">
						<h5>Variant</h5>
						<?= song::$variant_types[ $song['variant_type'] ]; ?>
					</div>
				<?php endif; ?>
				
				<div class="data__item">
					<h5>Appeared</h5>
					<?= $song['date_occurred'] ?: '?'; ?>
				</div>
				
				<div class="data__item">
					<h5><?= lang('Appearances', '登場回数', 'hidden'); ?></h5>
					<?= $song['num_appearances']; ?>
				</div>
				
				<?php if( $song['length'] ): ?>
					<div class="data__item">
						<h5><?= lang('Length', '時間', 'hidden'); ?></h5>
						<?= $song['length'] ?: '?'; ?>
					</div>
				<?php endif; ?>
				
			</div>
			
		</div>
		
		<!-- Original (if variant or cover) -->
		<?php if( is_array($song['original']) && !empty($song['original']) ): ?>
			<h3>
				<?= lang('Original song', '過去に発表した曲', 'div'); ?>
			</h3>
			
			<div class="text text--outlined">
				<?php
					if( is_numeric($song['cover_of']) ) {
						echo
							'<a class="symbol__artist" href="/artists/'.$song['original']['artist']['friendly'].'/">'.
							( $song['original']['artist']['romaji'] ? lang($song['original']['artist']['romaji'], $song['original']['artist']['name'], 'parentheses') : $song['original']['artist']['name'] ).
							'</a>'.
							'<br />';
					}
					echo
						'<a class="symbol__song" href="'.$song['original']['url'].'">'.
							( $song['original']['romaji'] ? lang($song['original']['romaji'], $song['original']['name'], 'parentheses') : $song['original']['name'] ).
						'</a>';
				?>
			</div>
		<?php endif; ?>
		
		<!-- Variants -->
		<?php if( is_array($song['variants']) && !empty($song['variants']) ): ?>
			<h3>
				<?= lang('Variants', '別バージョン', 'div'); ?>
			</h3>
			
			<ul class="text text--outlined">
				<?php foreach( $song['variants'] as $variant ): ?>
					<li>
						<a class="symbol__song" href="<?= $variant['url']; ?>"><?= $variant['romaji'] ? lang($variant['romaji'], $variant['name'], 'parentheses') : $variant['name']; ?><?= $variant['hint'] ? ' <span class="any__note">'.$variant['hint'].'</span>' : null; ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		
		<!-- Covers -->
		<?php if( is_array($song['covers']) && !empty($song['covers']) ): ?>
			<h3>
				<?= lang('Covers', 'カバー', 'div'); ?>
			</h3>
			
			<ul class="text text--outlined">
				<?php foreach( $song['covers'] as $cover ): ?>
					<li>
						<a class="symbol__artist" href="<?= '/artists/'.$cover['artist']['friendly'].'/'; ?>"><?= $cover['artist']['romaji'] ? lang($cover['artist']['romaji'], $cover['artist']['name'], 'parentheses') : $cover['artist']['name']; ?></a>
						<br />
						<a class="symbol__song" href="<?= $cover['url']; ?>"><?= $cover['romaji'] ? lang($cover['romaji'], $cover['name'], 'parentheses') : $cover['name']; ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		
		<!-- Notes -->
		<?php if( is_array($song['notes']) && !empty($song['notes']) ): ?>
			
			<h3>
				<?= lang('Notes', 'ノート', 'div'); ?>
			</h3>
			
			<ul class="text text--outlined">
				<?php foreach($song['notes'] as $note): ?>
					
					<li><?= $note; ?></li>
					
				<?php endforeach; ?>
			</ul>
			
		<?php endif; ?>
		
		<?php if( $song['video'] ): ?>
			<h3>
				<?= lang( 'Video', '映像', 'div' ); ?>
			</h3>
			<div class="any--margin">
				<a class="video__thumbnail" href="<?= '/videos/'.$song['video']['id'].'/&autoplay=1'; ?>" style="background-image:url(<?= $song['video']['image_url']; ?>);"></a>
			</div>
		<?php endif; ?>
		
		<!-- Releases -->
		<h3>
			<?= lang( 'Appears on', '所属するリリース', 'div' ); ?>
		</h3>
		
		<ul class="text">
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
		
	</div>
	
	<!-- Other songs -->
	<div>
		
		<h3>
			<?= lang( 'All songs by '.($artist['romaji'] ?: $artist['name']), $artist['name'].'の曲一覧', 'div' ); ?>
		</h3>
		
		<ul class="text text--outlined ul--compact any--weaken-color">
			<?php foreach($songs as $song): ?>
				<li class="any--flex">
					
					<a class="any--flex-grow" href="<?= $song['url']; ?>">
						<?= $song['romaji'] ? lang($song['romaji'], $song['name'], 'parentheses') : $song['name']; ?> <?= $song['hint'] ? '<span class="any__note">'.$song['hint'].'</span>' : null; ?>
					</a>
					
				</li>
			<?php endforeach; ?>
		</ul>
		
	</div>
	
</div>