<?php

include_once('../votes/function-render_vote.php');
include_once('../php/class-vote.php');

$vote = new vote($pdo);

script([
	'/development/script-issues.js',
]);

style([
	'/development/style-page-issues.css',
	'/development/style-page-index.css',
]);

$page_title = 'Latest development update';

?>

<div class="col c1">
	<div class="col--prose">
			
		<!-- Updates -->
		<div class="col--main">
			
			<?php if($_SESSION['is_boss']): ?>
				<a class="entry__edit symbol__edit" href="<?= '/development/'.$entry['id'].'/edit/'; ?>">edit</a>
			<?php endif; ?>
			
			<h2><?= lang('Latest update', 'サイト更新', 'div'); ?></h2>
			
			<div class="text text--prose">
				
				<?= $entry['content']; ?>
				
				<a class="a--outlined a--padded" href="<?= '/development/'.$entry['id'].'/#comments'; ?>" style="margin-top:3rem;">
					<?= lang('add comment', 'コメントする', 'hidden'); ?>
				</a>
				
			</div>
			
			<div class="any--margin">
				<a class="symbol__previous" href="/development/all/">all past updates</a>
			</div>
			
			<div class="text text--outlined any--weaken">
				All development on vkgy is handled by a very small team&mdash;if you like our work, please consider helping us through <a class="a--inherit" href="https://patreon.com/vkgy">our Patreon</a>. As always, thank you for your support.
			</div>
			
		</div>
		
		<!-- Issues -->
		<div class="col--side">
			
			<?php
				if($_SESSION['is_boss']) {
					?>
						<input class="issues__options-checkbox input__choice" id="show_controls" type="checkbox" />
						<label class="issues__options-button input__button" for="show_controls"></label>
					<?php
				}
			?>
			
			<h3>
				<?= lang('Issues', '問題', 'div'); ?>
			</h3>
			
			<input class="obscure__input" type="checkbox" <?= $issues ? 'checked' : null; ?> />
			<ul class="text text--outlined obscure__container obscure--faint issues__container">
				
				<?php if($issues): ?>
					
					<?php foreach($issues as $issue): ?>
						
						<li class="any--weaken">
							<form class="issue__container">
								
								<!-- Vote -->
								<div class="issue__vote">
									<?php
										$item_type = 'development';
										$item_id = $issue['id'];
										$issue['votes'] = $vote->access_vote([ 'item_type' => $item_type, 'item_id' => $item_id, 'get' => 'basics' ])[0];
										
										echo render_component($vote_template, [
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
								
								<div class="issue__text <?= $issue['is_completed'] ? 'issue--completed' : null; ?>">
									<span class="any__note"><?= '#'.$issue['id']; ?></span>
									<?= $issue['title']; ?>
								</div>
								
							</form>
						</li>
						
					<?php endforeach; ?>
					
					<a class="obscure__link a--padded a--outlined" href="/development/issues/">all issues</a>
					
				<?php else: ?>
					
					<span class="any--weaken">No open issues at this time.</span><br />
					<a class="a--padded a--outlined" href="/development/issues/" style="margin-top: 1rem;">past issues</a>
					
				<?php endif; ?>
				
			</ul>
			
		<?php include('partial-add_issue.php'); ?>
		
		</div>
		
	</div>
	
</div>

<div class="row">
	<?php include('../main/partial-patreon.php'); ?>
</div>