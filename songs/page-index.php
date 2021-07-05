<?php

// ========================================================
// Page setup
// ========================================================

$page_header = lang('Songs', '曲', 'div');

$page_title = 'Songs (曲)';

include('../songs/head.php');

// ========================================================
// Get additional data
// ========================================================

// Try to get songs with messed up flat names
$flat_songs = $access_song->access_song([ 'flat' => '-', 'get' => 'name' ]);

// Try to get songs missing romaji
$romaji_songs = $access_song->access_song([ 'friendly' => '-', 'get' => 'name' ]);

// Get all songs (we'll have to get rid of this once section grows)
$songs = $access_song->access_song([ 'get' => 'name', 'order' => 'artists.friendly ASC, songs.friendly ASC' ]);

// ========================================================
// Transform data
// ========================================================

$problem_songs = array_merge( $flat_songs, $romaji_songs );

?>

<div class="col c1">
	
	<?= $error ? '<div class="text text--outlined text--error">'.$error.'</div>' : null; ?>
	
	<!-- Songs with issues -->
	<?php if( $_SESSION['can_approve_data'] ): ?>
		<h2>
			<?= lang('Potential issues', '問題のある曲', 'div'); ?>
		</h2>
		
		<ul class="text ul--compact">
			
			<?php foreach( $problem_songs as $problem_song ): ?>
				<li>
					
					<?= $problem_song['romaji'] ? lang($problem_song['romaji'], $problem_song['name'], 'parentheses') : $problem_song['name']; ?>
					<a class="symbol__edit" href="<?= $problem_song['url'].'edit/'; ?>">edit</a>
					
				</li>
			<?php endforeach; ?>
			
		</ul>
	<?php endif; ?>
	
	<!-- All songs -->
	<h2>
		<?= lang('All songs', '全曲', 'div'); ?>
	</h2>
	
	<ul class="text">
		<?php foreach($songs as $song): ?>
			<li>
				<a class="symbol__song" href="<?= $song['url']; ?>"><?= $song['romaji'] ? lang($song['romaji'], $song['name'], 'parentheses') : $song['name']; ?></a>
				<a class="symbol__edit" href="<?= $song['url'].'edit/'; ?>">edit</a>
			</li>
		<?php endforeach; ?>
	</ul>
	
</div>