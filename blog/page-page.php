<?php

if($artist) {
	
	$page_title = $artist['quick_name'].' news | '.$artist['name'].'のニュース';
	$page_description = 'Latest news for visual kei band '.$artist['quick_name'].'. ビジュアル系バンド「'.$artist['name'].'」最新ニュース、情報のまとめ';
	
}

?>

<div class="col c1">
	<div>
		<?php
			if(!empty($error)) {
				?>
					<div class="text text--outlined text--error symbol__error">
						<?php echo $error; ?>
					</div>
				<?php
			}
		?>
		
		
		<?php
			if($artist || $tag) {
				$header_slug_en = $artist ? '<a class="artist" href="/artists/'.$artist['friendly'].'/">'.$artist['quick_name'].'</a>' : '<a class="symbol__tag" href="/blog/tag/'.$tag['friendly'].'/">'.$tag['tag'].'</a>';
				$header_slug_jp = $artist ? '<a class="artist" href="/artists/'.$artist['friendly'].'/">'.$artist['name'].'</a>' : '<a class="symbol__tag" href="/blog/tag/'.$tag['friendly'].'/">'.$tag['tag'].'</a>';
				
				?>
					<h2 class="any--margin">
						<?php
							echo lang(
								'Showing news tagged <span class="any__note" style="display: inline-block; vertical-align: top;">'.$header_slug_en.'</span>',
								$header_slug_jp.' のニュース',
								[ 'container' => 'div' ]);
						?>
					</h2>
				<?php
			}
		?>
	</div>

	<!--<div class="col c3 any--margin">
		<div>
			<?php
				$prev_link  = '/blog'.($_GET['artist'] ? '/artist/'.friendly($_GET['artist']) : null).($_GET['tag'] ? '/tag/'.friendly($_GET['tag']) : null);
				$prev_link .= '/page/'.$prev_next['prev']['page'].'/';
				
				$prev_text_en  = 'Page '.($prev_next['prev']['page'] ?: '1');
				$prev_text_jp  = ($prev_next['prev']['page'] ?: '1').' ページ目';
			
				subnav([
					[
						'text' => lang($prev_text_en, $prev_text_jp, ['secondary_class' => 'any--hidden']),
						'url' => $prev_next['prev']['page'] ? $prev_link : null,
						'position' => 'left',
					]
				], 'directional');
				
				//echo $prev_next['prev']['page'] ? '<a href="'.$prev_link.'"><span class="symbol__previous"></span> '.lang($prev_text_en, $prev_text_jp, ['secondary_class' => 'any--hidden']).'</a>' : lang($prev_text_en, $prev_text_jp, ['secondary_class' => 'any--hidden']);
			?>
		</div>
		<div style="text-align: center;">
			<?php
				if(is_numeric($_GET['page'])) {
					$start = (sanitize($_GET['page']) * 10 - 10 + 1);
					$stop = (sanitize($_GET['page']) * 10 - 10 + count($entries));
				
					//echo lang('Entries '.$start.' to '.$stop, $start.'～'.$stop.'件', [ 'secondary_class' => 'any--hidden' ]);
					
					
			
					subnav([
						[
							'text' => lang('Entries '.$start.' to '.$stop, $start.'～'.$stop.'件', [ 'secondary_class' => 'any--hidden' ]),
							'position' => 'center',
						]
					], 'directional');

				}
				else { 
					//echo lang('Latest', '最近', [ 'secondary_class' => 'any--hidden' ]);
					subnav([
						[
							'text' => lang('Latest', '最近', [ 'secondary_class' => 'any--hidden' ]),
							'position' => 'center',
						]
					], 'directional');
				}
			?>
		</div>
		<div style="text-align: right;">
			<?php
				$next_link  = '/blog'.($_GET['artist'] ? '/artist/'.friendly($_GET['artist']) : null).($_GET['tag'] ? '/tag/'.friendly($_GET['tag']) : null);
				$next_link .= '/page/'.$prev_next['next']['page'].'/';
				
				$next_text_en  = 'Page '.($prev_next['next']['page'] ?: $prev_next['latest_page_num']);
				$next_text_jp  = ($prev_next['next']['page'] ?: $prev_next['latest_page_num']).' ページ目';
				
				subnav([
					[
						'text' => lang($next_text_en, $next_text_jp, ['secondary_class' => 'any--hidden']),
						'url' => $prev_next['next']['page'] ? $next_link : null,
						'position' => 'right',
					]
				], 'directional');
				
				//echo $prev_next['next']['page'] ? '<a href="'.$next_link.'">'.lang($next_text_en, $next_text_jp, ['secondary_class' => 'any--hidden']).' <span class="symbol__next"></span></a>' : lang($next_text_en, $next_text_jp, ['secondary_class' => 'any--hidden']);
			?>
		</div>
	</div>-->
</div>

<div class="col c4-AAAB">
	<div>

		<?php
			for($i=0; $i<$num_entries; $i++) {
				?>
					<div class="entry__container text any__obscure any__obscure--fade lazy" data-src="<?php echo is_array($entries[$i]['image']) && !empty($entries[$i]['image']) ? str_replace('.', '.small.', $entries[$i]['image']['url']) : null; ?>">
						<h2>
							<div class="h5">
								<?php echo $entries[$i]['date_occurred']; ?>
								<a class="user" data-icon="<?= $entries[$i]['user']['icon']; ?>" data-is-vip="<?= $entries[$i]['user']['is_vip']; ?>" href="<?= $entries[$i]['user']['url']; ?>"><?= $entries[$i]['user']['username']; ?></a>
							</div>
							<a href="/blog/<?php echo $entries[$i]["friendly"]; ?>/"><?php echo $entries[$i]["title"]; ?></a>
						</h2>
						<?php
							echo $markdown_parser->parse_markdown($entries[$i]["content"]);
						?>
						<a class="a--padded a--outlined entry__comment" style="margin-top:1rem;" href="<?php echo '/blog/'.$entries[$i]['friendly'].'/'; ?>"><?php echo lang('comment on this', 'コメントする', [ 'secondary_class' => 'any--hidden' ]); ?></a>
					</div>
				<?php
			}
		?>
	</div>

	<div>
		<?php
			if(is_array($queued_entries) && !empty($queued_entries)) {
				?>
					<h3>
						<div class="h5">
							Admin
						</div>
						<?php echo lang('Queued entries', '出す予定', ['container' => 'div']); ?>
					</h3>
					<ul class="text text--outlined ul--compact">
						<?php
							foreach($queued_entries as $entry) {
								?>
									<li>
										<a href="<?php echo '/blog/'.$entry['friendly'].'/'; ?>"><?php echo $entry['title']; ?></a>
									</li>
								<?php
							}
						?>
					</ul>
				<?php
			}
		?>
		
		<h3>
			<?php echo lang('Popular', 'おすすめ', [ 'container' => 'div' ]); ?>
		</h3>
		<ul class="text text--outlined ul--compact">
			<?php
				$sql_rec = 'SELECT blog.title, blog.friendly, COUNT(*) AS num_comments FROM comments LEFT JOIN blog ON blog.id=comments.item_id WHERE comments.item_type=0 AND comments.date_occurred>"'.date('Y-m-d', strtotime('-2 months')).'" GROUP BY comments.item_id ORDER BY num_comments DESC LIMIT 10';
				$stmt_rec = $pdo->prepare($sql_rec);
				$stmt_rec->execute();
				$rslt_rec = $stmt_rec->fetchAll();
				$rslt_rec = is_array($rslt_rec) ? $rslt_rec : [];
				shuffle($rslt_rec);
				
				for($i=0; $i < count($rslt_rec) && $i < 5; $i++) {
					?>
						<li>
							<a href="<?php echo '/blog/'.$rslt_rec[$i]['friendly'].'/'; ?>"><?php echo $rslt_rec[$i]['title']; ?></a>
						</li>
					<?php
				}
			?>
		</ul>

		<h3>
			<?php echo lang('Browse by tag', 'タグ', [ 'container' => 'div' ]); ?>
		</h3>
		<ul class="ul--compact text text--outlined">
			<?php
				$sql_tags = "SELECT tags.friendly, tags.tag, COUNT(*) AS num_tagged FROM blog_tags LEFT JOIN tags ON tags.id=blog_tags.tag_id GROUP BY blog_tags.tag_id ORDER BY tags.friendly ASC";
				$stmt_tags = $pdo->prepare($sql_tags);
				$stmt_tags->execute();

				foreach($stmt_tags->fetchAll() as $tag) {
					?>
						<li>
							<span class="any__note" style="float:right;"><?php echo $tag['num_tagged']; ?></span>
							<a class="symbol__tag" href="<?php echo '/blog/tag/'.$tag["friendly"]; ?>/"><?php echo $tag["tag"]; ?></a>
						</li>
					<?php
				}
			?>
		</ul>
	</div>
</div>