<?php

include_once('../votes/function-render_vote.php');
include_once('../php/class-vote.php');

$vote = new vote($pdo);

script([
	'/about/script-issues.js',
]);

?>

<div class="col c2">
	
	<!-- Updates -->
	<div>
		
		<h2>
			<?= lang('Site updates', 'サイト更新', 'div'); ?>
		</h2>
		
		<ul class="text">
			
			<?php foreach($entries as $entry_key => $entry): ?>
				<li style="clear: both;">
					
					<?php if($entry_key > 0): ?>
						<a class="a--padded" href="<?= '/about/development/'.$entry['id'].'/#comments'; ?>" style="float: right; z-index: 1;">
							<?= lang('read entry', '読む', 'hidden'); ?>
						</a>
					<?php endif; ?>
					
					<div class="h5">
						<?= $entry['date_occurred']; ?>
						<?= $entry_key === 0 ? '<span style="color:hsl(var(--accent));">NEW</span>' : null; ?>
					</div>
					
					<a class="h2" href="<?= '/about/development/'.$entry['id'].'/'; ?>">
						<?= $entry['title']; ?>
					</a>
					
					<?php if($entry_key === 0): ?>
						<br /><br />
						<p class="any--weaken">
							<?= $entry['content'].'...'; ?>
						</p>
						<br />
						
						<a class="a--outlined a--padded" href="<?= '/about/development/'.$entry['id'].'/#comments'; ?>">
							<?= lang('add comment', 'コメントする', 'hidden'); ?>
						</a>
					<?php endif; ?>
					
				</li>
			<?php endforeach; ?>
			
		</ul>
		
	</div>
	
	<!-- Issues -->
	<div>
		
		<h2>
			<?= lang('Issues', '問題', 'div'); ?>
		</h2>
		
		<!--<div class="text text--outlined any--weaken">
			<?php if( $_SESSION['is_vip'] ): ?>
				<a href="https://patreon.com/vkgy" target="_blank">VIP members</a> can upvote issues and help decide which ones take priority.
			<?php else: ?>
				Please upvote any issues that you agree with, to help us prioritize development.
			<?php endif; ?>
		</div>-->
		
		<ul class="text text--outlined issues__container">
			
			<?php foreach($issues as $issue): ?>
				<li>
					<form class="issue__container any--flex any--weaken-size">
						
						<div class="issue__text <?= $issue['is_completed'] ? 'issue--completed' : null; ?>" style="margin-right:auto;">
							<span class="any__note"><?= '#'.$issue['id']; ?></span>
							<?= $issue['title']; ?>
						</div>
						
						<?php /*if($_SESSION['is_moderator']): ?>
						<input name="id" value="<?= $issue['id']; ?>" hidden />
						
						<label class="issue__completed-label input__checkbox">
							<input class="issue__completed input__choice" name="is_completed" type="checkbox" value="1" <?= $issue['is_completed'] ? 'checked' : null; ?> />
							<span class="symbol__unchecked"></span>
							<span data-role="status"></span>
						</label>
						<?php endif;*/ ?>
						
						<div class="issue__vote">
							<?php
								$item_type = 'development';
								$item_id = $issue['id'];
								$issue['votes'] = $vote->access_vote([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'basics' ])[0];
								
								echo render_component($vote_template, [
									//'direction_class' => 'vote--vertical',
									'item_id' => $item_id,
									'item_type' => $item_type,
									'upvote_is_checked' => $issue['votes']['user_score'] > 0 ? 'checked' : null,
									'downvote_is_checked' => $issue['votes']['user_score'] < 0 ? 'checked' : null,
									'score' => $issue['votes']['score'] ?: 0,
								]);
							?>
						</div>
						
					</form>
				</li>
			<?php endforeach; ?>
			
		</ul>
		
		<style>
			.issue__container {
				justify-content: space-between;
			}
			.issue--completed {
				opacity: 0.75;
				text-decoration: line-through;
			}
			.issue__completed-label {
				margin: 0 0 0 0.5rem;
			}
			.issue__completed + .symbol__unchecked::before {
				margin-right: 0 !important;
			}
			.issue__completed ~ [data-role="status"] {
				margin-left: 1rem;
			}
			.issue__completed ~ [data-role="status"]::before {
				opacity: 1;
			}
			.issue__completed ~ [data-role="status"]:not([class*="symbol"]) {
				display: none;
			}
		</style>
		
		<?php include('partial-add_issue.php'); ?>
		
	</div>
	
</div>