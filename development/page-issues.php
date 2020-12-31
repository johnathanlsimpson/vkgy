<?php

include_once('../votes/function-render_vote.php');
include_once('../php/class-vote.php');

$vote = new vote($pdo);

script([
	'/development/script-issues.js',
]);

style([
	'/development/style-page-issues.css',
]);

$page_title = 'Issues';

?>

<div class="col c2">
	
	<!-- Open issues -->
	<div>
		
		<?php
			if($_SESSION['is_boss']) {
				?>
					<input class="issues__options-checkbox input__choice" id="show_controls" type="checkbox" />
					<label class="issues__options-button input__button" for="show_controls"></label>
				<?php
			}
		?>
		
		<h2>
			<?= lang('Open issues', '問題', 'div'); ?>
		</h2>
		
		<ul class="text issues__container">
			<?php foreach($issues['incomplete'] as $issue): ?>
				
				<li>
					<form class="issue__container">
						
						<div class="issue__vote">
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
						
						<!-- Completion toggle -->
						<?php if($_SESSION['is_boss']): ?>
							<input name="id" value="<?= $issue['id']; ?>" hidden />
							<input class="issue__completed input__choice" id="<?= 'issue-'.$issue['id']; ?>" name="is_completed" type="checkbox" value="1" <?= $issue['is_completed'] ? 'checked' : null; ?> />
							<label class="issue__completed-label input__checkbox symbol__unchecked" for="<?= 'issue-'.$issue['id']; ?>">done?</label>
						<?php endif; ?>
						
						<!-- Tag -->
						<label class="issue__right any__note"><?= $issue['issue_type'] ? ['other', 'bug', 'feature'][$issue['issue_type']] : 'other'; ?></label>
						
						<a class="issue__text" href="<?= '/development/'.$issue['id'].'/'; ?>">
							<?= $issue['title']; ?>
						</a>
						
						<div class="any--weaken" style="margin-top: 1rem;">
							<?= '#'.$issue['id']; ?> &middot;
							<?= substr($issue['date_occurred'], 0, 10); ?>
							<?= $issue['user'] ? '&middot; by '.$access_user->render_username($issue['user'], 'a--inherit') : null; ?>
						</div>
						
					</form>
				</li>
				
			<?php endforeach; ?>
		</ul>
		
	</div>
	
	<!-- Closed issues -->
	<div>
		
		<!-- Add issue -->
		<?php include('partial-add_issue.php'); ?>
		
		<h3>
			<?= lang('Completed issues', '問題', 'div'); ?>
		</h3>
		
		<ul class="text text--outlined any--weaken">
			<?php foreach($issues['completed'] as $issue): ?>
				
				<li>
					
					<a class="issue__right symbol__next" href="<?= '/development/'.$issue['id'].'/'; ?>">comment</a>
					<span class="any__note">#<?= $issue['id']; ?></span>
					<?= $issue['title']; ?>
					
				</li>
				
			<?php endforeach; ?>
		</ul>
		
	</div>
	
</div>