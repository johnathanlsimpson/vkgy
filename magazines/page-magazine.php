<?php

// ========================================================
// Page setup
// ========================================================

$page_header = lang('About '.($magazine['romaji'] ?: $magazine['name']), $magazine['name'].'の詳細', 'div');

$page_title = $magazine['romaji'] ? $magazine['romaji'].' ('.$magazine['name'].')' : $magazine['name'];

subnav([
	lang('Magazine', '叢書の情報', 'hidden') => $magazine['url'],
]);

// Need to switch for more generic permission
if( $_SESSION['can_add_livehouses'] ) {
	subnav([
		lang('Edit magazine', '叢書を編集する', 'hidden') => $magazine['url'].'edit/',
	], 'interact', true);
}

?>

<div class="col c1">
	
	<div class="text">
		
		<h2>
			<?= $magazine['romaji'] ? lang($magazine['romaji'], $magazine['name'], 'div') : $magazine['name']; ?>
		</h2>
		
		<div class="data__container">
			
			<?php if($_SESSION['can_approve_data']): ?>
				<div class="data__item">
					<div class="h5">ID</div>
					<?= $magazine['id']; ?>
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
					
					<div class="h5"><?= lang('Companies', '関係会社', 'hidden'); ?></div>
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
					
					<a href="<?= $issue['url']; ?>">
						
						<?php if($issue['image']): ?>
							<img alt="<?= $issue['romaji'] ?: $issue['name']; ?>" src="<?= $issue['image']['thumbnail_url']; ?>" style="height:100%;width:100%;object-fit:cover;object-position:center;float:left;margin-right:1rem;width:3rem;height:3rem;display:inline-block;border-radius:var(--border-radius);overflow:hidden;" />
						<?php else: ?>
							<span class="any--crossed-out" style="float:left;margin-right:1rem;background:hsl(var(--background));height:3rem;width:3rem;display:inline-block;border-radius:var(--border-radius);"></span>
						<?php endif; ?>
						
						<span class="h5" style="float:right;margin: 0.25rem 0 0.5rem 0.5rem;"><?= substr($issue['date_represented'], 0, 7); ?></span>
						
						<?php
							$issue_name   = $magazine['name'].' <span class="any__note">'.$issue['full_volume_name'].'</span>';
							$issue_romaji = $magazine['romaji'] || $issue['full_volume_romaji'] ? ($magazine['romaji'] ?: $magazine['name']).' <span class="any__note">'.($issue['full_volume_romaji'] ?: $issue['full_volume_name']).'</span>' : null;
							echo $issue_romaji ? lang($issue_romaji, $issue_name, 'div') : $issue_name;
						?>
						
					</a>
					
					<div style="clear:both;width:100%;height:0;"></div>
					
				</li>
			<?php endforeach; ?>
		<?php else: ?>
			<span class="symbol__error">This magazine doesn't have any issues.</span> <?= $_SESSION['can_add_data'] ? '<a class="" href="/magazines/add-issue/">Add issue?</a>' : null; ?>
		<?php endif; ?>
		
	</ul>
	
</div>

<style>
	#language-en:checked~* .magazine__issue .any--ja .any__note {
		background: inherit;
		color: inherit;
		font: inherit;
		padding: 0;
	}
</style>