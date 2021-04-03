<?php

include_once('../musicians/head.php');

$page_header = lang('Musicians', 'メンバー', 'div');

?>

<?php if($error): ?>
	<div class="col c1">
		<div>
			<div class="text text--outlined error symbol__error"><?= $error; ?></div>
		</div>
	</div>
<?php endif; ?>

<?php include('../search/page-musicians.php'); ?>