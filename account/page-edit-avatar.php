<?php
	
	$user['username'] = $_SESSION['username'];
	include('head-user.php');
	
	$page_header = lang('Edit avatar', 'アバター変更', 'div');
	
	$avatar = new avatar(null, $rslt_avatar, [ 'is_vip' => $_SESSION['is_vip'] ]);
	$current_avatar = $avatar->get_selected_options();
	
?>

<div class="col c1 user__edit" >
	<?php
		include('../avatar/partial-edit.php');
	?>
</div>