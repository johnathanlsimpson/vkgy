<?php

$page_title = tr('{username} lists', ['replace'=>['username'=>$user['username']]]);

$page_header = tr('{username} lists', ['ja'=>'{username}のリスト','lang'=>true,'lang_args'=>'div','replace'=>['username'=>$user['username']]]);

include_once('head-user.php');

include_once('../php/class-access_list.php');

$access_list = new access_list($pdo);
$lists = $access_list->access_list([ 'user_id' => $user['id'], 'get' => 'basics' ]);

?>

<div class="col c1">
	<div>
		<?php include('partial-card.php'); ?>
	</div>
</div>

<div class="col c1" id="activity">
	<div>
		
		<h2>
			<?= lang('Lists', 'リスト', 'div'); ?>
		</h2>
		
		<div class="list-card__wrapper">
			
			<?php foreach($lists as $list): ?>
				
				<div class="list-card__container text">
					
					<a class="list-card__link" href="<?= '/lists/'.$list['id'].'/'.$list['friendly'].'/'; ?>"></a>
					
					<div class="list-card__image"></div>
					
					<span class="list-card__name h2"><?= $list['name']; ?></span>
					
					<span class="list-card__num any__note"><?= $list['num_items']; ?> items</span>
					
					<div class="list-card__details any--weaken any--flex">
						
						<span class="list-card__date"><?= substr($list['date_occurred'], 0, 10); ?></span>
						
						by&nbsp;<?= $access_user->render_username($list['user'], 'list-card__user a--inherit'); ?>
						
					</div>
					
				</div>
				
			<?php endforeach; ?>
			
		</div>
		
		<style>
			.list-card__wrapper {
				display: grid;
				grid-gap: 1rem;
				grid-template-columns: repeat( auto-fit, minmax(250px, 1fr) );
			}
			
			.list-card__container {
				margin: 0;
			}
			.list-card__link {
				border-radius: inherit;
				bottom: -1px;
				left: -1px;
				position: absolute;
				right: -1px;
				top: -1px;
				z-index: 1;
			}
			.list-card__link:hover {
				border: 1px solid hsl(var(--interactive));
			}
			.list-card__link:hover ~ .list-card__name {
				color: hsl(var(--interactive));
			}
			.list-card__image {
				background: hsl(var(--background--secondary));
				height: 100px;
				margin: -1rem;
				margin-bottom: 1rem;
			}
			.list-card__num {
				float: right;
			}
			.list-card__details {
				margin-top: 1rem;
			}
			.list-card__date {
				margin-right: auto;
			}
			.list-card__user {
				z-index: 2;
			}
		</style>
		
	</div>
</div>