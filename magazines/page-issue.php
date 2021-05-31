<?php

// ========================================================
// Page setup
// ========================================================

$issue_name   = $issue['name'].' '.$issue['full_volume_name'];
$issue_romaji = $issue['romaji'] || $issue['full_volume_romaji'] ? ($issue['romaji'] ?: $issue['name']).' '.($issue['full_volume_romaji'] ?: $issue['full_volume_name']) : null;

$page_header = lang( 'About '.($issue_romaji ?: $issue_name), $issue_name.'の詳細', 'div' );

$page_title = $issue_romaji ? $issue_romaji.' ('.$issue_name.')' : $issue_name;

subnav([
	lang('Magazine', '叢書の情報', 'hidden') => $issue['magazine_url'],
	lang('Issue', '雑誌の情報', 'hidden') => $issue['url'],
]);

if( $_SESSION['can_add_data'] ) {
	subnav([
		lang('Edit issue', '雑誌を編集する', 'hidden') => $issue['url'].'edit/',
	], 'interact', true);
}

?>

<div class="col c1">
	
	<div class="text">
		
		<?php if( $issue['images'] ): ?>
			<a href="<?= $issue['image']['url']; ?>">
				<img alt="<?= $issue['romaji'] ?: $issue['name']; ?>" height="<?= $issue['image']['height']; ?>" width="<?= $issue['image']['width']; ?>" src="<?= $issue['image']['small_url']; ?>" style="height:auto;max-height:400px;max-width:300px;float:left;margin-right:1rem;" />
			</a>
		<?php endif; ?>
		
		<!-- Title -->
		<h2 class="issue__title">
			<?php
				if( $issue['full_volume_romaji'] || $issue['romaji'] ) {
					echo lang(
						($issue['romaji'] ?: $issue['name']).' <span class="a--outlined">'.($issue['full_volume_romaji'] ?: $issue['full_volume_name']).'</span>',
						$issue['name'].' <span class="a--outlined">'.$issue['full_volume_name'].'</span>',
						'div'
					);
				}
				else {
					echo $issue['name'].' <span class="a--outlined">'.$issue['full_volume_name'].'</span>';
				}
			?>
		</h2>
		
		<!-- Details -->
		<div class="data__container">
			
			<!-- Date for -->
			<div class="data__item">
				<div class="h5"><?= lang('Date', '公開日', 'hidden'); ?></div>
				<?= substr($issue['date_represented'], 0, 7) ?: '?'; ?>
			</div>
			
			<!-- Price -->
			<div class="data__item">
				<div class="h5"><?= lang('Price', '価格', 'hidden'); ?></div>
				<?= $issue['price'] ? lang($issue['price'], str_replace(' yen', '円', $issue['price']), 'hidden') : '?'; ?>
			</div>
			
			<!-- Date sold -->
			<?php if($issue['date_occurred']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Date sold', '予定日', 'hidden'); ?></div>
					<?= substr($issue['date_occurred'], 0, 10); ?>
				</div>
			<?php endif; ?>
			
			<!-- Product number -->
			<?php if($issue['product_number']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Product number', '品番', 'hidden'); ?></div>
					<?= $issue['product_number']; ?>
				</div>
			<?php endif; ?>
			
			<!-- JAN code -->
			<?php if($issue['jan_code']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('JAN code', 'JANコード	', 'hidden'); ?></div>
					<?= $issue['jan_code']; ?>
				</div>
			<?php endif; ?>
			
		</div>
		
	</div>
	
	<!-- Artists -->
	<h2>
		<?= lang('Contents', 'コンテンツ', 'div'); ?>
	</h2>
	
	<ul class="text text--outlined">
		
		<?php if( is_array($issue['artists']) && !empty($issue['artists']) ): ?>
			<?php foreach($issue['artists'] as $group_name => $artists): ?>
				
				<li class="any--weaken-color">
					
					<h5>
						<?= [ 'is_cover' => 'Cover features', 'is_large' => 'Large features', 'is_normal' => 'Other appearances', 'is_flyer' => 'Flyers' ][ $group_name ]; ?>
					</h5>
					
					<?php foreach($artists as $index => $artist): ?>
						<?= $index ? ', ' : null; ?>
						<a class="artist" href="<?= '/artists/'.$artist['friendly'].'/'; ?>"><?= $artist['romaji'] ? lang($artist['romaji'], $artist['name'], 'parentheses') : $artist['name']; ?></a>
					<?php endforeach; ?>
					
				</li>
				
			<?php endforeach; ?>
		<?php else: ?>
		<span class="symbol__error">This issue doesn't have any artists listed. <?= $_SESSION['can_add_data'] ? '<a href="'.$issue['url'].'edit/">Edit issue?</a>' : null; ?></span>
		<?php endif; ?>
		
	</ul>
	
</div>

<style>
	#language-en:checked~* .issue__title .any--ja .a--outlined {
		background: inherit;
		border: inherit;
		box-shadow: inherit;
		color: inherit;
		font: inherit;
		padding: 0;
	}
</style>