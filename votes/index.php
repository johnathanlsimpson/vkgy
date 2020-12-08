<?php

include_once('../votes/function-render_vote.php');

$sql_vote = 'SELECT score FROM votes_development WHERE item_id=? AND user_id=? LIMIT 1';
$stmt_vote = $pdo->prepare($sql_vote);
$stmt_vote->execute([ 1, $_SESSION['user_id'] ]);
$rslt_vote = $stmt_vote->fetchColumn();

?>

<div class="col c1">
	<div>
		<?php
			echo render_component($vote_template, [
				'item_id' => 1,
				'item_type' => 'development',
				'direction_class' => 'vote--vertical',
				'upvote_is_checked' => $rslt_vote == 1 ? 'checked' : null,
				'downvote_is_checked' => $rslt_vote == -1 ? 'checked' : null,
				'score' => 5,
			]);
			
			echo render_component($vote_template, [
				'item_id' => 2,
				'item_type' => 'development',
				'upvote_is_checked' => 'checked',
				'downvote_is_checked' => null,
				'score' => 5,
			]);
		?>
	</div>
</div>