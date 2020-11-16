<div class="col c2">
	
	<!-- Upvotes -->
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
		
		<ul class="text ">
			
			<?php foreach($issues as $issue): ?>
				<li class="any--flex any--weaken-size" style="flex-wrap:wrap;">
					
					<div class="" style="flex:1;">
						
						<span class="any__note"><?= '#'.$issue['id']; ?></span>
						
						<?= $issue['title']; ?>
						
					<div class="h5" style="width: 100%;">
					</div></div>
					
				</li>
			<?php endforeach; ?>
			
		</ul>
		
		<?php include('partial-add_issue.php'); ?>
		
	</div>
	
</div>