<?php
	if($_SESSION["is_boss"]) {
		$queue_directory = "../images/queue";
		
		$suppress_auto_upload_image = true;
		include_once("../images/function-upload_image.php");
		
		if(file_exists($queue_directory)) {
			$queued_files = array_values(array_diff(scandir($queue_directory), [".", ".."]));
			
			shuffle($queued_files);
			
			if(is_array($queued_files) && !empty($queued_files)) {
				for($i = 0; $i < count($queued_files) && $i < 50; $i++) {
					$source_image_path = "../images/queue/".$queued_files[$i];
					
					upload_image([
						"name"              => "vkgy-exclusive.jpg",
						"type"              => "image/jpg",
						"tmp_name"          => $source_image_path,
						"error"             => 0,
						"queued"            => true,
						"needs_compression" => false
					], $pdo);
				}
				$output = '<span class="any__note">'.$i.'</span> images added to queue.';
			}
			else {
				$output = "No images in temporary queue.";
			}
		}
		
		$page_header = 'Add images to queue';
		
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--notice">
						<?php echo $output; ?>
					</div>
				</div>
			</div>
		<?php
	}
?>