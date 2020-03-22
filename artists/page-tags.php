<h3 class="h--nav">
	<span><?php echo lang('Tags', 'タグ', ['container' => 'div']); ?></span>
	<a class="a--padded a--outlined" style="" href="/search/artists/#tags">View all</a>
</h3>
<div class="text text--outlined">
	<?php
		if(is_array($rslt_curr_tags) && !empty($rslt_curr_tags)) {
			foreach($rslt_curr_tags as $tag) {
				echo '<a class="symbol__tag" style="display: inline-block;" href="/search/artists/?tags[]='.$tag["friendly"].'">'.
					lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).
					($tag["num_times_tagged"] > 0 ? ' <span class="any__note">×'.$tag["num_times_tagged"].'</span>' : null).
					'</a> &nbsp; ';
			}
			echo '<hr />';
		}
		
		echo '<h5>'.lang('Add tags', 'タグする', ['secondary_class' => 'any--hidden']).'</h5>';
		if($_SESSION["loggedIn"]) {
			if(is_array($rslt_tags) && !empty($rslt_tags)) {
				foreach($rslt_tags as $tag) {
					$is_selected = in_array($tag["id"], $rslt_user_tags);
					echo '<label data-id="'.$artist["id"].'" data-tag_id="'.$tag["id"].'" class="artist__tag any__tag symbol__tag '.($is_selected ? "any__tag--selected" : null).'" style="display: inline-block;">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</label> ';
				}
			}
		}
		else {
			echo '<span class="symbol__error"><a class="a--inherit" href="/account/">Sign in</a> to add tags.';
		}
		
		if($_SESSION["is_editor"] > 0 && $needs_admin_tags) {
			echo '<hr />';
			echo '<h5>Remove admin tags</h5>';
			
			if(is_array($rslt_tags) && !empty($rslt_tags)) {
				foreach($rslt_tags as $tag) {
					if($tag["is_admin_tag"] && in_array($tag["id"], $rslt_curr_tag_ids)) {
						echo '<label data-action="delete" data-id="'.$artist["id"].'" data-tag_id="'.$tag["id"].'" class="artist__tag symbol__tag any__tag any__tag--selected" style="display: inline-block;">'.lang(($tag["romaji"] ?: $tag["name"]), $tag['name'], ['secondary_class' => 'any--hidden']).'</label> ';
					}
				}
			}
		}
	?>
</div>