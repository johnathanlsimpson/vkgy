<?php
	
	$page_title = tr('{username} activity', ['replace'=>['username'=>$user['username']]]);
	
	$page_header = tr('{username} activity', ['ja'=>'{username}の活動','lang'=>true,'lang_args'=>'div','replace'=>['username'=>$user['username']]]);
	
	include('head-user.php');
?>

<div class="col c1">
	<div>
		<?php include('partial-card.php'); ?>
	</div>
</div>

<div class="col c1" id="activity">
	<div>
		<h2>
			<?= tr('{username} activity', ['ja'=>'{username}の活動','lang'=>true,'lang_args'=>'div','replace'=>['username'=>$user['username']]]); ?>
		</h2>
		
		<?php
			
			// Get any pagination, filtering, etc
			parse_str($_SERVER['REQUEST_URI'], $url_query);
			array_shift($url_query);
			
			// Set limit/page
			$activity_limit = 100;
			$activity_offset = (is_numeric($url_query['page']) ? max( ($url_query['page'] - 1), 0 ) : 0) * $activity_limit;
			$activity_order = $url_query['order'] === 'asc' ? 'ASC' : 'DESC';
			$activity_filter = in_array($url_query['filter'], ['all', 'discussion', 'additions', 'edits', 'other']) ? friendly($url_query['filter']) : null;
			
			// Set canon URL
			$canon_url = '/users/'.$user['username'].'/activity/';
			$canon_filter = in_array($url_query['filter'], ['all', 'discussion', 'additions', 'edits', 'other']) ? '&filter='.friendly($url_query['filter']) : null;
			$canon_page = is_numeric($url_query['page']) && $url_query['page'] ? '&page='.$url_query['page'] : null;
			$canon_order = $url_query['order'] === 'asc' ? '&order=asc' : null;
			
		?>
		
		<div class="filter__container">
			<a class="input__radio input__radio--selected <?= ($url_query['order'] === 'asc' ? 'symbol__triangle symbol--up' : 'symbol__triangle symbol--down'); ?>" href="<?= $canon_url.$canon_filter.'&order='.($url_query['order'] === 'asc' ? 'desc' : 'asc').'#activity'; ?>"><?= tr('date', ['ja' => '年月日']); ?></a>
			<div>
				<span class="symbol__filter symbol--standalone"></span>
				<a href="<?= $canon_url.$canon_order.'#activity'; ?>"                   class="label search__filter input__radio <?= !in_array($url_query['filter'], ['discussion', 'additions', 'edits', 'other']) ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>"><?= tr('all', ['ja'=>'全て']); ?></a>
				<a href="<?= $canon_url.$canon_order.'&filter=discussion#activity'; ?>" class="label search__filter input__radio <?= $url_query['filter'] === 'discussion'                                          ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>"><?= tr('discussion', ['ja'=>'会話']); ?></a>
				<!--<a href="<?= $canon_url.$canon_order.'&filter=additions#activity'; ?>"  class="label search__filter input__radio <?= $url_query['filter'] === 'additions'                                       ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>"><?= tr('additions', ['ja'=>'新規作成']); ?></a>-->
				<a href="<?= $canon_url.$canon_order.'&filter=edits#activity'; ?>"      class="label search__filter input__radio <?= $url_query['filter'] === 'edits'                                               ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>"><?= tr('edits', ['ja'=>'編集']); ?></a>
				<a href="<?= $canon_url.$canon_order.'&filter=other#activity'; ?>"      class="label search__filter input__radio <?= $url_query['filter'] === 'other'                                               ? 'symbol__checked input__radio--selected' : 'symbol__unchecked'; ?>"><?= tr('other', ['ja'=>'その他']); ?></a>
			</div>
		</div>
		
		<style>
			.filter__container {
				display: flex;
				flex-wrap: wrap;
			}
			.filter__container .symbol--standalone {
				display: none;
				margin: 0 1ch 0.5rem 0;
			}
			.filter__container > a:last-of-type {
				margin-right: auto;
			}
			.filter__container a {
				margin: 0 1ch 0.5rem 0;
				white-space: nowrap;
			}
			.filter__container div {
				display: flex;
				flex-wrap: wrap;
				margin-right: -0.5rem;
				width: 100%;
			}
			@media(min-width:800px) {
				.filter__container .symbol--standalone {
					display: initial;
				}
				.filter__container div {
					width: auto;
				}
			}
		</style>
		
		<?php
			ob_start();
			
			?>
				<div class="any--weaken-color any--flex" style="justify-content: space-between; margin: 0.5rem 0 1rem 0; ">
					<div>
						<?php
							if(is_numeric($url_query['page']) && $url_query['page'] > 1) {
								?>
									<a class="symbol__previous" href="<?= $canon_url.$canon_filter.$canon_order.'&page='.($url_query['page'] - 1).'#activity'; ?>"><?= tr('Page {page_num}', ['replace'=>['page_num'=>($url_query['page'] - 1)]]); ?></a>
								<?php
							}
							else {
								echo tr('Page {page_num}', ['replace'=>['page_num'=>'1']]);
							}
						?>
					</div>
					<div style="text-align: center;">
						<?= tr('Results {start_num} to {end_num}', [
							'replace' => [
								'start_num' => ($activity_offset + 1),
								'end_num' => ($activity_offset + $activity_limit)
							]
						]); ?>
					</div>
					<div style="text-align: right;">
						<a class="symbol__next" href="<?= $canon_url.$canon_filter.$canon_order.'&page='.(is_numeric($url_query['page']) && $url_query['page'] ? $url_query['page'] + 1 : 2).'#activity'; ?>"><?= tr('Page {page_num}', ['replace'=>['page_num'=>(is_numeric($url_query['page']) && $url_query['page'] ? $url_query['page'] + 1 : 2)]]); ?></a>
					</div>
				</div>
			<?php
			
			$pagination = ob_get_clean();
			//echo $pagination;
		?>
		
		<ul class="text">
			<?php include('partial-activity.php'); ?>
		</ul>
		
		<?php // echo $pagination; ?>
	</div>
</div>