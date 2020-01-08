<?php

$page_header = lang('Edit avatar', 'アバター変更', 'div');

if($_SESSION['is_signed_in']) {

	// Get selected avatar options
	$sql_avatar = "SELECT content FROM users_avatars WHERE user_id=? LIMIT 1";
	$stmt_avatar = $pdo->prepare($sql_avatar);
	$stmt_avatar->execute([ $_SESSION['user_id'] ]);
	$rslt_avatar = $stmt_avatar->fetchColumn();
	$rslt_avatar = $rslt_avatar ?: '{"head__base":"default","head__base-color":"i"}';
	
	//$avatar = new avatar($avatar_layers, $rslt_avatar, ["is_vip" => true]);
	//$user["avatar"] = $avatar->get_avatar_paths();
	
}

 ?>
			<!-- Edit -->
			<?php
				//if($_SESSION["username"] === $user["username"]) {
					?>
						<div class="col c1 user__edit" >
							<?php
								include_once("../avatar/class-avatar.php");
								
								$avatar = new avatar(null, $rslt_avatar, [ 'is_vip' => $_SESSION['is_vip'] ]);
								$current_avatar = $avatar->get_selected_options();
								
								include('../avatar/partial-edit.php');
							?>
						</div>
					<?php
				//}
			?>