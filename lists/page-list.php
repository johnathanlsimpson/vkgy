<?php

include_once('../php/class-parse_markdown.php');
$markdown_parser = new parse_markdown($pdo);

?>

<div class="col c1">
	
	<div class="col c4-ABBC" style="margin: 0 auto; width: 1000px; max-width: 100%;">
		<span style="width:80px;"></span>

		<div>
			
			<a class="list__avatar" href="/users/Ryu/"><img class="lazy" data-src="https://vk.gy/usericons/avatar-inartistic.png" /></a>
			
			<h1 class="list__title">
				<a class="a--inherit" href="<?= '/lists/'.$list['id'].'/'.$list['friendly'].'/'; ?>"><?= $list['name']; ?></a>
				
				<div class="h5">
					by <?= $access_user->render_username($list['user'], 'a--inherit'); ?>
				</div>
			</h1>
			
			
		
		</div>
		
		<span style="width:200px;"></span>
	</div>
	
	<div class="col c4-ABBC" style="margin: 0 auto; width: 1000px; max-width: 100%;">
		
		<span style="width: 80px;"></span>
		
		<div>

			<ul class="">

				<?php foreach($list['items'] as $item): ?>
				<li class="list__item">

					<?= $item['content']; ?>

					<div class="list__details any--weaken">

						<div class="data__container">

							<div class="data__item">
								<h5>
									type
								</h5>
								<?= array_search($item['item_type'], $access_list->allowed_item_types); ?>
							</div>

							<div class="data__item">
								<h5>
									added
								</h5>
								<?= substr($item['date_added'], 0, 10); ?>
							</div>

						</div>

					</div>
				</li>
				<?php endforeach; ?>

			</ul>

		</div>
		
		<div style="width: 200px;">
		
			<ul class="text text--outlined any--weaken">
				
				<li>
					
					
					<h5>
						List by
					</h5>
					<?= $access_user->render_username($list['user'], 'a--inherit'); ?>
					<br style="clear:both;" />
				</li>
				
				<li>
					<h5>
						Created
					</h5>
					<?= substr($list['date_occurred'], 0, 10); ?>
				</li>
				
				<li>
					<h5>
						Num items
					</h5>
					<?= count($list['items']); ?>
				</li>
				
			</ul>
			
			<?php
				$user_lists = $access_list->access_list([ 'user_id' => $list['user']['id'], 'get' => 'basics' ]);
				if( is_array($user_lists) && !empty($user_lists) ) {
					
					echo '<h3>Other lists</h3>';
					
					foreach($user_lists as $user_list) {
						
						?>
							<a class="list__card text text--outlined text--compact" href="<?= '/lists/'.$user_list['id'].'/'.$user_list['friendly'].'/'; ?>">
								<span class="list__count any__note"><?= $user_list['num_items']; ?> item<?= $user_list['num_items'] != 1 ? 's' : null; ?></span>
								<?= $user_list['name']; ?>
							</a>
						<?php
						
					}
					
				}
			?>
			
		</div>

	</div>

</div>

<style>
	.list__avatar {
		align-items: center;
		background: hsl(var(--background));
		border-radius: 50%;
		display: inline-flex;
		float: left;
		flex-wrap: wrap;
		height: 50px;
		margin: 0;
		margin-right: 0.5rem;
		justify-content: center;
		width: 50px;
	}
	.list__avatar img {
		height: 60px;
		top: -5px;
		width: 60px;
	}
	.list__card {
		display: block;
		margin-bottom: 1rem;
	}
	.list__count {
		float: right;
		margin: 0 0 0.5rem 0.5rem;
	}
	
	.list__item {
	}
	@media(min-width:800px) {
		.list__details {
			left: calc(-80px - var(--gutter));
			margin: 0;
			position: absolute;
			top: 0;
			width: 80px;
		}
	}
	
	.module--youtube {
		border-radius: 3px;
		max-width: none !important;
		width: 100%;
	}
	.module--youtube .youtube__embed {
		margin: -1rem;
		margin-bottom: 0;
		max-width: none;
		width: calc(100% + 2rem);
	}
	.module--youtube .h2::before {
		display: none;
	}
	
	.module--release {
		padding: 0;
		width: 100%;
	}
	.module--release > :first-child {
		width: 100%;
	}
</style>