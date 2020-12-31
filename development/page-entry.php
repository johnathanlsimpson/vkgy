<?php

include_once('../votes/function-render_vote.php');
include_once('../php/class-vote.php');

$vote = new vote($pdo);

style([
	'/development/style-page-index.css',
]);

if($entry['is_issue']) {
	$active_page = '/development/issues/';
	$page_title = 'Issue #'.$entry['id'];
}

else {
	$active_page = '/development/all/';
	$page_title = $entry['title'];
}

?>

<div class="col c2">
	
	<!-- Current entry -->
	<div>
		
		<!-- Updates -->
		<div class="col--main">
			
			<?php if($_SESSION['is_boss']): ?>
				<a class="entry__edit symbol__edit" href="<?= '/development/'.$entry['id'].'/edit/'; ?>">edit</a>
			<?php endif; ?>
			
			<?php if($entry['is_issue']): ?>
				
				<h2>
					<?= lang('Issue <span class="any__note">#'.$entry['id'].'</span>', '問題 #'.$entry['id'], 'div'); ?>
				</h2>
				
				<div class="text text--outlined">
					
					<!-- Vote -->
					<div class="issue__vote issue__right">
						<?php
							$item_type = 'development';
							$item_id = $issue['id'];
							$issue['votes'] = $vote->access_vote([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'basics' ])[0];
							
							echo render_component($vote_template, [
								'direction_class' => 'vote--vertical',
								'item_id' => $item_id,
								'item_type' => $item_type,
								'upvote_is_checked' => $issue['votes']['user_score'] > 0 ? 'checked' : null,
								'downvote_is_checked' => $issue['votes']['user_score'] < 0 ? 'checked' : null,
								'score' => $issue['votes']['score'] ?: 0,
							]);
						?>
					</div>
					
					<?= $entry['title']; ?>
					
					<div class="data__container any--weaken-color" style="margin-top: 1rem; transform: translateX(-0.5rem);">
						
						<div class="data__item">
							<h5>
								Type
							</h5>
							<?= $entry['issue_type'] ? ['other', 'bug', 'feature'][$entry['issue_type']] : 'other'; ?>
						</div>
						
						<div class="data__item">
							<h5>
								Requested on
							</h5>
							<?= $entry['date_occurred']; ?>
						</div>
						
						<?php if($entry['user']): ?>
							<div class="data__item">
								<h5>
									Requested by
								</h5>
								<?= $access_user->render_username($entry['user'], 'a--inherit'); ?>
							</div>
						<?php endif; ?>
						
						<div class="data__item">
							<h5>
								Status
							</h5>
							<?= $entry['is_completed'] ? '<span class="symbol__success">completed</span>' : '<span class="symbol__error">incomplete</span>'; ?>
						</div>
						
					</div>
					
				</div>
				
				<a class="symbol__previous" href="/development/issues/">all past issues</a>
				
			<?php else: ?>
				
				<h2>
					<div class="h5"><?= $entry['date_occurred']; ?> by <?= $access_user->render_username($entry['user'], 'a--inherit'); ?></div>
					<?= $entry['title']; ?>
				</h2>
				
				<div class="text text--prose">
					<?= $entry['content']; ?>
				</div>
				
				<a class="symbol__previous" href="/development/all/">all past updates</a>
				
			<?php endif; ?>
			
		</div>
		
	</div>
	
	<!-- Comments -->
	<div>
		<?php
			include('../comments/partial-comments.php');
			render_default_comment_section('development', $entry['id'], $entry['comments'], $markdown_parser);
		?>
	</div>
	
</div>