<?php
	breadcrumbs([
		"Lives" => "/lives/"
	]);
	
	subnav([
		"Edit livehouses" => "/lives/livehouses/edit/",
		"Add livehouses" => "/lives/add/livehouses/",
		//"Edit areas" => "/lives/edit/areas/",
	]);
	
	script([
		"/scripts/external/script-selectize.js",
		"/scripts/script-initSelectize.js",
		"/lives/script-page-edit-livehouses.js"
	]);
	
	style([
		"/style/external/style-selectize.css",
		"/style/style-selectize.css",
	]);
	
	$pageTitle = "Lives";
	
	// Select page type
	if($_SESSION["admin"]) {
		if($_GET["method"] === "livehouses") {
			if($_GET["action"] === "edit") {
				$edit_livehouses = true;
			}
			elseif($_GET["action"] === "add") {
				$add_livehouses = true;
			}
		}
		elseif($_GET["method"] === "areas") {
			$edit_areas = true;
		}
	}
	if($_GET['page'] === 'livehouses') {
		$view_livehouses = true;
	}
	
	
	// Get data: add/edit livehouses
	if($add_livehouses || $edit_livehouses) {
		$sql_areas = "SELECT id, name, romaji FROM lives_areas ORDER BY friendly ASC";
		$stmt_areas = $pdo->prepare($sql_areas);
		$stmt_areas->execute();
		$rslt_areas = $stmt_areas->fetchAll();
		foreach($rslt_areas as $key => $rslt) {
			$area_list[$key] = [$rslt["id"], "", ($rslt["romaji"] ? $rslt["romaji"].' ('.$rslt["name"].')' : $rslt["name"])];
		}
		
		$sql_companies = "SELECT id, name, romaji FROM labels ORDER BY friendly ASC";
		$stmt_companies = $pdo->prepare($sql_companies);
		$stmt_companies->execute();
		$rslt_companies = $stmt_companies->fetchAll();
		foreach($rslt_companies as $key => $rslt) {
			$company_list[$key] = [$rslt["id"], "", ($rslt["romaji"] ? $rslt["romaji"].' ('.$rslt["name"].')' : $rslt["name"])];
		}
		
		$sql_all_livehouses = "SELECT name, romaji, id FROM lives_livehouses ORDER BY friendly ASC";
		$stmt_all_livehouses = $pdo->prepare($sql_all_livehouses);
		$stmt_all_livehouses->execute();
		$rslt_all_livehouses = $stmt_all_livehouses->fetchAll();
		$num_all_livehouses = count($rslt_all_livehouses);
		for($i=0; $i<$num_all_livehouses; $i++) {
			$livehouse_list[] = [
				$rslt_all_livehouses[$i]["id"],
				"",
				str_replace(["&#92;", "&#34;"], ["\\", "\""], $rslt_all_livehouses[$i]["area_romaji"].' '.($rslt_all_livehouses[$i]["romaji"] ?: $rslt_all_livehouses[$i]["name"]).' ('.$rslt_all_livehouses[$i]["area_name"].$rslt_all_livehouses[$i]["name"].')')
			];
		}
		
		$limit_num = 10;
	}
	// Get add'l data: add livehouses
	if($add_livehouses) {
		$pageTitle = "Add livehouses";
		
		breadcrumbs([
			"Add livehouses" => "/lives/add/livehouses/",
		]);
	}
	// Get add'l data: edit livehouses
	if($edit_livehouses) {
		$pageTitle = "Edit livehouses";
		
		breadcrumbs([
			"Edit livehouses" => "/lives/edit/livehouses/",
		]);
		
		$max_page = ceil($num_all_livehouses / $limit_num);
		$page_num = $_GET["page"];
		$page_num = $page_num > 0 ? ($page_num <= $max_page ? $page_num : $max_page) : 1;
		$start_num = (($page_num - 1) * $limit_num);
		
		$sql_livehouses = "
			SELECT
				lives_livehouses.*,
				GROUP_CONCAT(lives_nicknames.nickname) AS nicknames,
				lives_areas.name AS area_name,
				lives_areas.romaji AS area_romaji,
				labels.name AS parent_name,
				labels.romaji AS parent_romaji,
				renamed_to.name AS renamed_name,
				renamed_to.romaji AS renamed_romaji,
				CONCAT_WS(' ', COALESCE(lives_areas.romaji, lives_areas.name), COALESCE(lives_livehouses.romaji, lives_livehouses.name)) AS quick_name
			FROM lives_livehouses
			LEFT JOIN lives_areas ON lives_areas.id = lives_livehouses.area_id
			LEFT JOIN lives_nicknames ON lives_nicknames.livehouse_id = lives_livehouses.id
			LEFT JOIN labels ON labels.id = lives_livehouses.parent_id
			LEFT JOIN lives_livehouses renamed_to ON renamed_to.id = lives_livehouses.renamed_to
			".(is_numeric($_GET['id']) ? 'WHERE lives_livehouses.id=?' : null)."
			GROUP BY lives_livehouses.id
			ORDER BY quick_name ASC
			".(!is_numeric($_GET['id']) ? 'LIMIT ?, ?' : null);
		$stmt_livehouses = $pdo->prepare($sql_livehouses);
		$stmt_livehouses->execute(is_numeric($_GET['id']) ? [ $_GET['id'] ] : [ $start_num, $limit_num ]);
		$rslt_livehouses = $stmt_livehouses->fetchAll();
		$num_livehouses = count($rslt_livehouses);
	}
	// Get data: areas
	if($edit_areas) {
		$pageTitle = "Edit livehouse areas";
		
		breadcrumbs([
			"Edit areas" => "/lives/edit/areas/",
		]);
		
		$sql_areas = "SELECT * FROM lives_areas ORDER BY friendly ASC";
		$stmt_areas = $pdo->prepare($sql_areas);
		$stmt_areas->execute();
		$rslt_areas = $stmt_areas->fetchAll();
		$num_areas = count($rslt_areas);
		
		foreach($rslt_areas as $key => $rslt) {
			$area_list[$key] = [$rslt["id"], "", $rslt["romaji"]. '('.$rslt["name"].')'];
		}
	}
	// Get data: view livehouses
	if($view_livehouses) {
		$pageTitle = 'Livehouse list';
		
		breadcrumbs([
			'Livehouses' => '/lives/livehouses/'
		]);
		
		$sql_livehouses = 'SELECT lives_livehouses.id, lives_livehouses.name, lives_livehouses.romaji, lives_livehouses.friendly, lives_livehouses.capacity, lives_areas.name AS area_name, lives_areas.romaji AS area_romaji, GROUP_CONCAT(lives_nicknames.nickname) AS nicknames FROM lives_livehouses LEFT JOIN lives_areas ON lives_areas.id=lives_livehouses.area_id LEFT JOIN lives_nicknames ON lives_nicknames.livehouse_id=lives_livehouses.id GROUP BY lives_livehouses.id ORDER BY lives_livehouses.friendly ASC';
		$stmt_livehouses = $pdo->prepare($sql_livehouses);
		$stmt_livehouses->execute();
		$rslt_livehouses = $stmt_livehouses->fetchAll();
		$num_livehouses = count($rslt_livehouses);
	}
?>

<div class="col c1">
	<div>
		<h1>
			Lives
		</h1>
	</div>
</div>

<?php
	if($add_livehouses || $edit_livehouses) {
		include("page-edit-livehouses.php");
	}
	
	elseif($edit_areas) {
		include("../lives/page-edit-areas.php");
	}
	
	elseif($view_livehouses) {
		include('page-livehouses.php');
	}
	
	else {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--notice symbol__error">
						Sorry, there's nothing to see here yet!
					</div>
				</div>
			</div>
		<?php
	}
?>