<?php

// ========================================================
// Page setup
// ========================================================
$issue_name   = $issue['name'].' '.$issue['volume_name'];
$issue_romaji = $issue['romaji'] || $issue['volume_romaji'] ? ($issue['romaji'] ?: $issue['name']).' '.($issue['volume_romaji'] ?: $issue['volume_name']) : null;

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

style([
	'/magazines/style-page-issue.css',
]);

// ========================================================
// Get additional data
// ========================================================

// Previous issue
$sql_prev = 'SELECT * FROM issues WHERE magazine_id=? AND friendly<? ORDER BY friendly DESC, date_represented DESC, id DESC LIMIT 1';
$stmt_prev = $pdo->prepare($sql_prev);
$stmt_prev->execute([ $issue['magazine_id'], $issue['issue_friendly'] ]);
$rslt_prev = $stmt_prev->fetch();

if( $rslt_prev ) {
	subnav([
		[
			'text' => $issue['romaji'] || $rslt_prev['volume_romaji'] ? lang( ($issue['romaji'] ?: $issue['name']).' '.($rslt_prev['volume_romaji'] ?: $rslt_prev['volume_name']), $issue['name'].' '.$rslt_prev['volume_name'], 'hidden' ) : $issue['name'].' '.$rslt_prev['volume_name'],
			'url' => $issue['magazine_url'].$rslt_prev['id'].'/'.$rslt_prev['friendly'].'/',
			'position' => 'left',
		],
	], 'directional');
}

// Next issue
$sql_next = 'SELECT * FROM issues WHERE magazine_id=? AND friendly>? ORDER BY friendly ASC, date_represented ASC, id ASC LIMIT 1';
$stmt_next = $pdo->prepare($sql_next);
$stmt_next->execute([ $issue['magazine_id'], $issue['issue_friendly'] ]);
$rslt_next = $stmt_next->fetch();

if( $rslt_next ) {
	subnav([
		[
			'text' => $issue['romaji'] || $rslt_next['volume_romaji'] ? lang( ($issue['romaji'] ?: $issue['name']).' '.($rslt_next['volume_romaji'] ?: $rslt_next['volume_name']), $issue['name'].' '.$rslt_next['volume_name'], 'hidden' ) : $issue['name'].' '.$rslt_next['volume_name'],
			'url' => $issue['magazine_url'].$rslt_next['id'].'/'.$rslt_next['friendly'].'/',
			'position' => 'right',
		],
	], 'directional');
}

// ========================================================
// Transform data
// ========================================================

// Set up names used for purchase links
$amazon_name = $issue_name;
$cdj_name = ( $issue['romaji'] ?: $issue['name'] ).' '.( $issue['date_represented'] ? date( 'F Y', strtotime( $issue['date_represented'] ) ) : null );
$rh_name = $issue_romaji ?: $issue_name;

?>

<?= $error ? '<div class="col c1"><div class="text text--outlined text--error symbol__error">'.$error.'</div></div>' : null; ?>

<div class="col c1">
	
	<div class="text">
		
		<?php if( $issue['images'] ): ?>
			<a href="<?= $issue['image']['url']; ?>">
				<img class="issue__image" alt="<?= $issue['romaji'] ?: $issue['name']; ?>" height="<?= $issue['image']['height']; ?>" width="<?= $issue['image']['width']; ?>" src="<?= $issue['image']['medium_url']; ?>" />
			</a>
		<?php endif; ?>
		
		<!-- Title -->
		<h2 class="issue__title">
			<?php
				if( $issue['volume_romaji'] || $issue['romaji'] ) {
					echo lang(
						'<a class="a--inherit" href="'.$issue['magazine_url'].'">'.( $issue['romaji'] ?: $issue['name'] ).'</a> <span class="a--outlined">'.( $issue['volume_romaji'] ?: $issue['volume_name'] ).'</a>',
						'<a class="a--inherit" href="'.$issue['magazine_url'].'">'.$issue['name'].'</a> <span class="a--outlined">'.$issue['volume_name'].'</a>',
						'div'
					);
				}
				else {
					echo '<a class="a--inherit" href="'.$issue['magazine_url'].'">'.$issue['name'].'</a> <span class="a--outlined">'.$issue['volume_name'].'</a>';
				}
			?>
		</h2>
		
		<!-- Details -->
		<div class="data__container">
			
			<!-- Date for -->
			<?php if($issue['date_represented']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Date', '公開日', 'hidden'); ?></div>
					<?= substr($issue['date_represented'], 0, 7) ?: '?'; ?>
				</div>
			<?php endif; ?>
			
			<!-- Date sold -->
			<?php if($issue['date_occurred']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Date sold', '予定日', 'hidden'); ?></div>
					<?= substr($issue['date_occurred'], 0, 10); ?>
				</div>
			<?php endif; ?>
			
			<!-- Price -->
			<?php if($issue['price']): ?>
				<div class="data__item">
					<div class="h5"><?= lang('Price', '価格', 'hidden'); ?></div>
					<?= lang($issue['price'], str_replace(' yen', '円', $issue['price']), 'hidden'); ?>
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
		
		<!-- Shop -->
		<div class="data__container">
			<div class="data__item">
				
				<div class="h5"><?= lang('Purchase', '購入', 'hidden'); ?></div>
				
				<a class="" href="<?= tracking_link( 'amazon', urlencode(html_entity_decode( $amazon_name )), 'issue page' ); ?>" rel="nofollow" target="_blank">
					<img src="/releases/amazon.png" style="height:1rem;opacity:1;bottom:-2px;" /> Search Amazon<sup>JP</sup>
				</a>
				&nbsp;
				<a class="" href="<?= tracking_link( 'cdjapan', urlencode(html_entity_decode( $cdj_name )), 'issue page' ); ?>" target="_blank">
					<img src="/releases/cdj.gif" style="height:1rem;opacity:1;" /> <?= $release["upc"] ? 'Buy at' : 'Search'; ?> CDJapan
				</a>
				&nbsp;
				<a class="" href="<?= tracking_link( 'rarezhut', urlencode(html_entity_decode( $rh_name )), 'issue page' ); ?>" target="_blank">
					<img src="/releases/rh.gif" style="height:1rem;opacity:1;" /> Search RarezHut
				</a>
				
			</div>
		</div>
		
	</div>
	
	<!-- Artists -->
	<h2>
		<?= lang('Contents', 'コンテンツ', 'div'); ?>
	</h2>
	
	<ul class="text text--outlined">
		
		<?php if( is_array($issue['artists']) && !empty($issue['artists']) ): ?>
			<?php foreach($issue['artists'] as $group_name => $artists): ?>
				
				<li class="any--weaken-color issue__artists <?= 'issue--'.explode('_',$group_name)[1]; ?>">
					
					<h4 class="issue__artist-title">
						<?= [ 'is_cover' => 'Cover artists', 'is_large' => 'Large features', 'is_normal' => 'Other appearances', 'is_flyer' => 'Flyers' ][ $group_name ]; ?>
					</h4>
					
					<?php foreach($artists as $index => $artist): ?>
						<a class="issue__artist" href="<?= '/artists/'.$artist['friendly'].'/'; ?>">
							
							<?php if( image_exists( '/artists/'.$artist['friendly'].'/main.thumbnail.jpg', $pdo ) ): ?>
								<span class="issue__artist-thumbnail">
									<img class="issue__artist-image" src="<?= '/artists/'.$artist['friendly'].'/main.thumbnail.jpg'; ?>" />
								</span>
							<?php else: ?>
								<span class="issue__artist-thumbnail any--crossed-out"></span>
							<?php endif; ?>
							
							<span><?= $artist['romaji'] ? lang($artist['romaji'], $artist['name'], 'div') : $artist['name']; ?></span>
							
						</a>
					<?php endforeach; ?>
					
				</li>
				
			<?php endforeach; ?>
		<?php else: ?>
		<span class="symbol__error">This issue doesn't have any artists listed. <?= $_SESSION['can_add_data'] ? '<a href="'.$issue['url'].'edit/">Edit issue?</a>' : null; ?></span>
		<?php endif; ?>
		
	</ul>
	
</div>