<?php

// ========================================================
// Page setup
// ========================================================

$page_header = lang('About '.($magazine['romaji'] ?: $magazine['name']), $magazine['name'].'の詳細', 'div');

$page_title = ( $magazine['romaji'] ? $magazine['romaji'].' ('.$magazine['name'].')' : $magazine['name'] ).' | vkei magazine (V系 雑誌)';

subnav([
	lang('Magazine', '叢書の情報', 'hidden') => $magazine['url'],
]);

// Need to switch for more generic permission
if( $_SESSION['can_add_livehouses'] ) {
	subnav([
		lang('Edit magazine', '叢書を編集する', 'hidden') => $magazine['url'].'edit/',
	], 'interact', true);
}

style([
	'/magazines/style-page-magazine.css',
]);

// ========================================================
// Get additional data
// ========================================================

// Previous magazine
$sql_prev = 'SELECT * FROM magazines WHERE friendly<? ORDER BY friendly DESC LIMIT 1';
$stmt_prev = $pdo->prepare($sql_prev);
$stmt_prev->execute([ $magazine['friendly'] ]);
$rslt_prev = $stmt_prev->fetch();

if( $rslt_prev ) {
	subnav([
		[
			'text' => $rslt_prev['romaji'] ? lang( $rslt_prev['romaji'], $rslt_prev['name'], 'hidden' ) : $rslt_prev['name'],
			'url' => '/magazines/'.$rslt_prev['friendly'].'/',
			'position' => 'left',
		],
	], 'directional');
}

// Next magazine
$sql_next = 'SELECT * FROM magazines WHERE friendly>? ORDER BY friendly ASC LIMIT 1';
$stmt_next = $pdo->prepare($sql_next);
$stmt_next->execute([ $magazine['friendly'] ]);
$rslt_next = $stmt_next->fetch();

if( $rslt_next ) {
	subnav([
		[
			'text' => $rslt_next['romaji'] ? lang( $rslt_next['romaji'], $rslt_next['name'], 'hidden' ) : $rslt_next['name'],
			'url' => '/magazines/'.$rslt_next['friendly'].'/',
			'position' => 'right',
		],
	], 'directional');
}

?>

<?= $error ? '<div class="col c1"><div class="text text--outlined text--error symbol__error">'.$error.'</div></div>' : null; ?>

<div class="col c1">
	
	<div class="text">
		
		<h2>
			<?= $magazine['romaji'] ? lang($magazine['romaji'], $magazine['name'], 'div') : $magazine['name']; ?>
		</h2>
		
		<div class="data__container">
			
			<?php if($magazine['type_name']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Format', '形式', 'hidden'); ?></div>
					<?= $magazine['type_romaji'] ? lang($magazine['type_romaji'], $magazine['type_name'], 'parentheses') : $magazine['type_name']; ?></a>
				</div>
			<?php endif; ?>
			
			<?php if($magazine['size_name']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Size', '判型', 'hidden'); ?></div>
					<?= $magazine['size_romaji'] ? lang($magazine['size_romaji'], $magazine['size_name'], 'hidden') : $magazine['size_name']; ?></a>
				</div>
			<?php endif; ?>
			
			<?php if($magazine['parent_magazine']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Parent magazine', '親雑誌', 'hidden'); ?></div>
					<a class="a--inherit" href="<?= $magazine['parent_magazine']['url']; ?>"><?= $magazine['parent_magazine']['romaji'] ? lang($magazine['parent_magazine']['romaji'], $magazine['parent_magazine']['name'], 'parentheses') : $magazine['parent_magazine']['name']; ?></a>
				</div>
			<?php endif; ?>
			
			<?php if($magazine['default_price']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Price', '価格', 'hidden'); ?></div>
					<?= lang($magazine['default_price'], str_replace(' yen', '円', $magazine['default_price']), 'hidden'); ?>
				</div>
			<?php endif; ?>
			
			<?php if( is_array($magazine['labels']) && !empty($magazine['labels']) ): ?>
				<div class="data__item">
					
					<div class="h5"><?= lang('Publishers', '関係会社', 'hidden'); ?></div>
					<?php foreach($magazine['labels'] as $index => $label): ?>
						
						<a class="a--inherit symbol__company" href="<?= $label['url']; ?>"><?= $label['romaji'] ? lang($label['romaji'], $label['name'], 'parentheses') : $label['name']; ?></a>
						<?= $index + 1 < count($magazine['labels']) ? ', ' : null; ?>
						
					<?php endforeach; ?>
					
				</div>
			<?php endif; ?>
			
		</div>
		
	</div>
	
	<h2>
		<?= lang('List of '.($magazine['romaji'] ?: $magazine['name']).' issues', $magazine['name'].'のバックナンバー', 'div'); ?>
	</h2>
	
	<ul class="text text--outlined">
		
		<?php if( is_array($magazine['issues']) && !empty($magazine['issues']) ): ?>
			<?php foreach($magazine['issues'] as $issue): ?>
				<li class="magazine__issue">
					
					<?= $_SESSION['can_add_data'] ? '<a class="symbol__edit" href="'.$issue['url'].'edit/" style="padding:0 0.5rem 0 0.25rem;margin-left:0.25rem;float:right;z-index:1;"></a>' : null; ?>
					
					<a href="<?= $issue['url']; ?>">
						
						<?php if($issue['image']): ?>
							<img class="issue__thumbnail" alt="<?= $issue['romaji'] ?: $issue['name']; ?>" src="<?= $issue['image']['thumbnail_url']; ?>" />
						<?php else: ?>
							<span class="issue__thumbnail any--crossed-out"></span>
						<?php endif; ?>
						
						<span class="h5 issue__date"><?= substr($issue['date_represented'], 0, 7); ?></span>
						
						<span class="symbol__magazine issue__symbol"></span>
						
						<?php
							$issue_name   = $magazine['name'].' <span class="any__note">'.$issue['volume_name'].'</span>';
							$issue_romaji = $magazine['romaji'] || $issue['volume_romaji'] ? ($magazine['romaji'] ?: $magazine['name']).' <span class="any__note">'.($issue['volume_romaji'] ?: $issue['volume_name']).'</span>' : null;
							echo $issue_romaji ? lang($issue_romaji, $issue_name, 'div') : $issue_name;
						?>
						
					</a>
					
					<div class="issue__clear"></div>
					
				</li>
			<?php endforeach; ?>
		<?php else: ?>
			<span class="symbol__error">This magazine doesn't have any issues.</span> <?= $_SESSION['can_add_data'] ? '<a class="symbol__plus" href="/magazines/add-issue/&magazine='.$magazine['friendly'].'">Add issue?</a>' : null; ?>
		<?php endif; ?>
		
	</ul>
	
</div>