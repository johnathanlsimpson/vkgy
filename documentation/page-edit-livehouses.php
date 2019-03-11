<div class="col c1">
	<div>
		<h2>
			Editing livehouses
		</h2>
		
		<input class="obscure__input" id="obscure-edit-livehouse-documentation" type="checkbox" checked />
		<div class="text text--outlined documentation__basic obscure__container obscure--faint">
			<ul class="ul--bulleted">
				<h4 class="obscure__item">
					Naming
				</h4>
				<li class="obscure__item">Use the &ldquo;most official&rdquo; name, i.e. typically the text in the logo.</li>
				<li class="obscure__item">Typically, the area should be excluded from the name, and instead selected from the &ldquo;area&rdquo; dropdown. Some livehouses include the location in their name as part of their branding, e.g. HOLIDAY NAGOYA; in these cases, the area can stay.</li>
				<li class="obscure__item">Additional spellings/nicknames can be added in the &ldquo;hints&rdquo; field (see below).</li>
				<li class="obscure__item">The &ldquo;url friendly name&rdquo; is automatically generated, but may be manually edited. This name must be unique for each livehouse.</li>
				<li class="obscure__item">In edge cases, use best judgment.</li>
				
				<h4 class="obscure__item">
					Nicknames
				</h4>
				<li class="obscure__item">Nicknames, when typed into an artist's bio, will automatically resolve to the full livehouse name, and create a live with that artist on that date.</li>
				<li class="obscure__item">Several nicknames are automatically generated, but others may be manually added. This is usually used for common misspellings, for livehouses whose actual names are annoying to type, etc.</li>
				<li class="obscure__item">Nicknames must be unique; no two livehouses can use the same nickname.</li>
				
				<h4 class="obscure__item">
					Name changes
				</h4>
				<li class="obscure__item">When a livehouse changes its name, it should be added as a new livehouse. Then, on the old entry, use the &ldquo;renamed to&rdquo; field to link to the new livehouse. That way, old lives will appear with the proper livehouse name.</li>
				
				<h4 class="obscure__item">
					Capacity
				</h4>
				<li class="obscure__item">Capacity can usually be found at the livehouse's official site. Search for the phrase &ldquo;キャパ&rdquo;.</li>
				<li class="obscure__item">Capacity can be added with or without separating comma (e.g. 1000 and 1,000 both work).</li>
				
				<h4 class="obscure__item">
					Multiple stages
				</h4>
				<li class="obscure__item">Multiple stages/performing areas have to be added as separate livehouses.</li>
				
				<h4 class="obscure__item">
					Area
				</h4>
				<li class="obscure__item">Try to use the most specific area.</li>
				<li class="obscure__item">Currently, areas can only be added by certain users. Contact <a class="user" href="/users/inartistic/">inartistic</a> if an area needs to be added.</li>
				
				<h4 class="obscure__item">
					Merging duplicates
				</h4>
				<li class="obscure__item">Duplicate livehouses may be merged. This should <em>only</em> be used when a livehouse is in the database with an incorrect name; if the livehouse has purposely changed its name, the new name should be treated as a separte livehouse (see &ldquo;name changes&rdquo; above).</li>
				<li class="obscure__item">Use the &ldquo;merge data from&rdquo; field to merge data <em>from</em> the selected livehouse into the livehouse when you are currently editing.</li>
				<li class="obscure__item">The name of the livehouse that you are currently editing (not the livehouse in the dropdown box) will be preserved. For all other fields, the database will attempt to fill in any blanks, and merge both together.</li>
				<li class="obscure__item">After merging two livehouses, the system will automatically edit any lives to reflect the correct livehouse. The livehouse that was selected in the &ldquo;merge data from&rdquo; field will be deleted.</li>
			</ul>
			
			<label class="obscure__button input__button" for="obscure-edit-livehouse-documentation">Show documentation</label> 
		</div>
	</div>
</div>