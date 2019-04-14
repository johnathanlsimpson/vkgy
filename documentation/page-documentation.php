<?php
	style('/documentation/style-page-documentation.css');
	
	if($_GET['documentation_page']) {
		$documentation_page = $_GET['documentation_page'];
		$documentation_page = strtoupper(substr($documentation_page, 0, 1)).substr($documentation_page, 1);
		$documentation_page = str_replace('-', ' ', $documentation_page);
		
		breadcrumbs([
			'Documentation' => '/documentation/',
			$documentation_page => '/documentation/'.$_GET['documentation_page'].'/'
		]);
	}
?>

<div class="col c3-AAB <?php echo $_GET['documentation_page'] ? null : 'documentation--external'; ?>">
	<div>
		<?php
			foreach($documentation_contents as $documentation_page => $documentation_content) {
				$documentation_page = strtoupper(substr($documentation_page, 0, 1)).substr($documentation_page, 1);
				$documentation_page = str_replace('-', ' ', $documentation_page);
				
				?>
					<h1>
						Documentation: <?php echo $documentation_page; ?>
					</h1>
				<?php
				
				echo render_documentation($documentation_content);
			}
		?>
	</div>
	<div>
		<?php include('../documentation/partial-nav.php'); ?>
	</div>
</div>