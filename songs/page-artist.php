<?php

// ========================================================
// Page setup
// ========================================================

style([
	'/songs/style-page-artist.css',
]);

$page_header = lang('Edit song', '曲を追加', 'div');

$page_title = ($artist['romaji'] ?: $artist['name']).' songs ('.$artist['name'].'の曲一覧)';

include('../songs/head.php');

// ========================================================
// Transform data
// ========================================================

// Loop through and attach variants to parents
foreach( $songs as $song_id => $song ) {
	
	if( is_numeric( $song['variant_of'] ) ) {
		
		$songs[ $song['variant_of'] ]['variants'][] = $song;
		unset( $songs[ $song['id'] ] );
		
	}
	
}

?>

<div class="col c1">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<h2>
		<?= lang( 'All songs by '.($artist['romaji'] ?: $artist['name']), $artist['name'].'の曲一覧', 'div' ); ?>
	</h2>
	
	<ul class="text any--weaken-color">
		
		<?php if( is_array($songs) && !empty($songs) ): ?>
			
			<?php foreach($songs as $song): ?>
				<li class="any--flex" style="flex-wrap:wrap;">
					
					<a class="any--flex-grow" href="<?= $song['url']; ?>">
						<?= $song['romaji'] ? lang($song['romaji'], $song['name'], 'parentheses') : $song['name']; ?> <?= $song['hint'] ? '<span class="any__note">'.$song['hint'].'</span>' : null; ?>
						<span class="h5"><?= ( $song['date_occurred'] ?: '?' ).( $song['date_occurred'] && $song['length'] ? ' &middot; ' : null ).$song['length']; ?></span>
					</a>
					
					<?= $_SESSION['can_approve_data'] ? '<a class="a--inherit symbol__edit song__edit" href="'.$song['url'].'edit/">edit</a>' : null; ?>
					
					<?php if( is_array($song['variants']) && !empty($song['variants']) ): ?>
						<?php foreach( $song['variants'] as $variant ): ?>
							<div style="margin-top:0.25rem;width:100%;">
								↳ 
								<a class="any--flex-grow" href="<?= $variant['url']; ?>">
									<?= $variant['romaji'] ? lang($variant['romaji'], $variant['name'], 'parentheses') : $variant['name']; ?> <?= $variant['hint'] ? '<span class="any__note">'.$variant['hint'].'</span>' : null; ?>
									<span class="h5"><?= ( $variant['date_occurred'] ?: '?' ).( $variant['date_occurred'] && $variant['length'] ? ' &middot; ' : null ).$variant['length']; ?></span>
								</a>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					
				</li>
			<?php endforeach; ?>
			
		<?php else: ?>
			<span class="symbol__error">No songs have been created yet. Songs will be automatically created as releases are added or edited.</span>
		<?php endif; ?>
		
	</ul>
	
</div>