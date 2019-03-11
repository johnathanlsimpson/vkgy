<div class="col c1">
	<div>
		<h2>
			Update areas
		</h2>
		<div class="text">
			<span data-contains="areas" hidden><?php echo json_encode($rslt_areas); ?></span>
			<?php
			//print_r($rslt_areas);
				for($i=0; $i<$num_areas; $i++) {
					?>
						<div class="input__row li">
							<div class="input__group">
								<label class="input__label">ID</label>
								<input class="input" name="id[]" size="3" value="<?php echo $rslt_areas[$i]["id"]; ?>" disabled />
							</div>
							<div class="input__group any--flex-grow">
								<label class="input__label">Name</label>
								<input class="input" name="name[]" value="<?php echo $rslt_areas[$i]["name"]; ?>" />
								<input class="input--secondary" name="romaji[]" value="<?php echo $rslt_areas[$i]["romaji"]; ?>" />
							</div>
							<div class="input__group">
								<label class="input__label">Friendly</label>
								<input class="input" value="<?php echo $rslt_areas[$i]["friendly"]; ?>" />
							</div>
							<div class="input__group">
								<label class="input__label">Parent</label>
								<select class="input" data-source="areas" name="parent_id[]">
									<option value="<?php echo $rslt_areas[$i]["parent_id"]; ?>" selected></option>
								</select>
							</div>
						</div>
					<?php
				}
			?>
		</div>
	</div>
</div>