<?php
	if($_SESSION["admin"]) {
		script([
			"/scripts/external/script-selectize.js",
			"/scripts/external/script-inputmask.js",
			"/scripts/script-initSelectize.js",
			"/scripts/script-initDelete.js",
			"/labels/script-page-edit.js"
		]);
		
		style([
			"/style/external/style-selectize.css",
			"/style/style-selectize.css",
			"/labels/style-page-edit.css"
		]);
		
		foreach($access_label->access_label(["get" => "list"]) as $key => $row) {
			$label_list[] = [$row["id"], "", $row["quick_name"].($row["romaji"] ? " (".$row["name"].")" : "")];
			unset($tmp_label_list[$key]);
		}
		
		unset($label["artists"]);
		
		$page_header = '<a class="a--inherit" href="/labels/'.$label['friendly'].'/"><span class="symbol__company"></span>'.$label['name'].'</a>';
		if($label['romaji']) {
			$page_header = lang(
				'<a class="a--inherit" href="/labels/'.$label['friendly'].'/"><span class="symbol__company"></span>'.$label['romaji'].'</a>',
				'<a class="a--inherit" href="/labels/'.$label['friendly'].'/"><span class="symbol__company"></span>'.$label['name'].'</a>',
				[ 'container' => 'div' ]
			);
		}
		
		subnav([
			lang('Label profile', 'レーベルプロフィール', ['secondary_class' => 'any--hidden']) => '/labels/'.$label['friendly'].'/',
			lang('Edit label', 'レーベルを編集', ['secondary_class' => 'any--hidden']) => '/labels/'.$label['friendly'].'/edit/',
		]);
		
		?>
			<div class="col c1">
				<form action="/labels/function-edit.php" enctype="multipart/form-data" method="post" name="form__edit">
					<span class="any--hidden" data-contains="companies" hidden><?php echo json_encode($label_list); unset($label_list); ?></span>
					
					
					<div class="text">
						<div class="input__row">
							<div class="input__group">
								<label class="input__label">ID</label>
								<input class="input" name="id" placeholder="id" size="3" value="<?php echo $label["id"]; ?>" readonly />
							</div>
							<div class="input__group any--flex-grow">
								<label class="input__label">Name</label>
								<input class="input" name="name" placeholder="name" value="<?php echo $label["name"]; ?>" />
								<input class="input--secondary" name="romaji" placeholder="(romaji)" value="<?php echo $label["romaji"]; ?>" />
							</div>
							<div class="input__group">
								<label class="input__label">Friendly</label>
								<input name="friendly" placeholder="url-friendly name" value="<?php echo $label["friendly"]; ?>" />
							</div>
							<div class="input__group">
								<label class="input__label">Parent company</label>
								<select class="input selectize" data-populate-on-click="true" data-source="companies" name="parent_label_id" placeholder="select parent company">
									<option></option>
									<option value="<?php echo $label["parent_label"]["id"]; ?>" selected><?php echo $label["parent_label"]["quick_name"]; ?></option>
								</select>
							</div>
						</div>
						
						<div class="input__row">
							<div class="input__group">
								<label class="input__label">President</label>
								<input name="president_id" placeholder="ID" size="3" value="<?php echo $label["president"]["id"]; ?>" />
							</div>
							<div class="input__group any--flex-grow">
								<input class="input" name="president_name" placeholder="name" value="<?php echo $label["president"]["name"]; ?>" />
								<input class="input--secondary" name="president_romaji" placeholder="(romaji)" value="<?php echo $label["president"]["romaji"]; ?>" />
							</div>
							<div class="input__group">
								<label class="input__label">Date started</label>
								<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_started" placeholder="yyyy-mm-dd" size="10" value="<?php echo $label["date_started"]; ?>" />
							</div>
							<div class="input__group">
								<label class="input__label">Date ended</label>
								<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" max-length="10" name="date_ended" placeholder="yyyy-mm-dd" size="10" value="<?php echo $label["date_ended"]; ?>" />
							</div>
						</div>
						
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<!--<label class="input__label">History</label>
								<textarea class="input__textarea any--flex-grow" name="history"><?php echo $label["history"]; ?></textarea>-->
								<label class="input__label">Official links</label>
								<textarea class="input__textarea any--flex-grow" name="official_links" placeholder="http://official.com/"><?php echo $label["official_links"]; ?></textarea>
							</div>
						</div>
					</div>
					
					<div class="text">
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<button class="any--flex-grow" name="submit" type="submit">
									Add labels
								</button>
								<span data-role="status"></span>
							</div>
							<div class="input__group">
								<label class="input__checkbox-label symbol__trash symbol--standalone" name="delete"></label>
							</div>
						</div>
						<div class="any--hidden text text--outlined text--notice edit__result" data-role="result"></div>
					</div>
				</form>
			</div>
		<?php
	}
	else {
		?>
			<div class="col c1">
				<div>
					<div class="text text--outlined text--error symbol__error">
						Sorry, only administrators may add labels.
					</div>
				</div>
			</div>
		<?php
		
		include("../labels/index.php");
	}
?>