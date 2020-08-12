<?php

include_once('../php/function-render_component.php');

$page_header = tr('Translate vkgy', [ 'lang' => true, 'lang_args' => 'div' ]);

subnav([
	'Add/edit' => '/translations/'
]);

script([
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/translations/script-index.js',
]);
	
style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/translations/style-page-index.css'
]);

?>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>

<div class="col c1">
	<div>
		
		<div class="text text--outlined text--notice symbol__error">
			<?= tr('This feature is currently in alpha. Editors may add translations for any available language, but the ability to view vkgy in that language won\'t come until the beta.'); ?>
		</div>
		
	</div>
</div>

<style>
	<?php
		foreach($translate->allowed_languages as $language_key => $language_key) {
			if($language_key != 'en') {
				foreach($translate->allowed_languages as $lang_key => $lang_name) {
					if($lang_key != 'en' && $lang_key != $language_key) {
						echo '[data-filter-lang="'.$language_key.'"] [data-language="'.$lang_key.'"] {display: none;}';
					}
				}
			}
		}
	?>
</style>

<div class="col c1">
	
	<h2>
		<?= tr('Translations', [ 'lang' => true, 'lang_args' => 'div' ]); ?>
	</h2>
	<h3 class="accepted__section">
		404
	</h3>
	
	<div class="col c4-AAAB">
	
	<!-- Strings and proposals -->
	<div id="translations-list" data-filter-lang="<?= $translate->language; ?>">
		
		<ul class="text list">
			
			<!-- Header -->
			<li class="accepted__row accepted__header">
				<label class="h5 accepted__en">English phrase</label>
				<label class="h5 accepted__more">&nbsp;</label>
				<?php
					foreach($translate->allowed_languages as $language_key => $language) {
						if($language_key != 'en') {
							echo '<label class="h5 accepted__lang" data-language="'.$language_key.'">'.$language_key.'</label>';
						}
					}
				?>
			</li>
			
			<!-- Translations -->
			<?php
				if(is_array($strings) && !empty($strings)) {
					foreach($strings as $string) {
						?>
							<li x-data="{open:false}">
								<form action="/translations/function-update_translation.php" class="accepted__row" enctype="multipart/form-data" method="post" name="add_translation[]">
								
								<span class="id any--hidden"><?= $string['id']; ?></span>
								<span class="section any--hidden"><?= $string['folder']; ?></span>
								
								<!-- Text -->
								<span class="accepted__en"><?= $string['content']; ?><?= $_SESSION['is_boss'] ? ' <span class="any--weaken"><a class="accepted__edit symbol__edit a--inherit"></a></span>' : null; ?></span>
								<span class="accepted__more"><a class="symbol__down-caret" x-on:click="open=!open">view</a></span>
								<?php
									foreach($translate->allowed_languages as $language_key => $language) {
										if($language_key != 'en') {
											echo '<span class="accepted__lang '.(is_numeric($string[$language_key.'_id']) ? 'symbol__checkbox--checked' : 'symbol__checkbox--unchecked').'" data-language="'.$language_key.'"></span>';
										}
									}
								?>
								
								<ul class="details__container text" x-show="open">
									
									<!-- View current translations -->
									<?php
										if(!$translation_template) {
											?>
												<template id="template-translation">
													
													<?php
														ob_start();
														?>
															<li class="any--flex details__proposal" data-language="{language}">
																<div class="details__content-container any--flex-grow any--weaken-color">
																	<span class="language any--hidden">{language}</span>
																	<h5>
																		{language_name}
																	</h5>
																	<span class="details__content" data-id="{id}">{content}</span>
																	<span class="details__accepted any__note {is_accepted}" data-language="{language}" data-id="{id}">accepted</span>
																</div>
																<span class="details__user any--weaken"><a class="user a--inherit" data-icon="{user_icon}" data-is-vip="{user_is_vip}">{user_username}</a></span>
																<span class="details__date any--weaken">{date_occurred}</span>
																
																<span class="tag__voting any--weaken-color">
																	<label class="tag__vote tag__upvote" data-vote="upvote" data-id="{id}">
																		<input class="tag__choice input__choice" type="checkbox" {upvote_is_checked} />
																		<span class="tag__status symbol__up-caret symbol--standalone"></span>
																	</label>
																	
																	<span class="tag__num any--weaken-size" data-id="{id}" data-num-tags="{num_votes}"></span>
																	
																	<label class="tag__vote tag__status tag__downvote" data-vote="downvote" data-id="{id}">
																		<input class="tag__choice input__choice" type="checkbox" {downvote_is_checked} />
																		<span class="symbol__down-caret symbol--standalone"></span>
																	</label>
																</span>
																
															</li>
														<?php
														
														$translation_template = ob_get_clean();
														echo preg_replace('/'.'\s+'.'/', ' ', $translation_template);
													?>
												</template>
											<?php
										}
										
										// Start a counter and assume each string is new (later we'll loop through proposals and update)
										$num_new[ $string['folder'] ][ $string['id'] ] = 1;
										
										$string['languages'] = [];
										if( is_array($proposals[$string['id']]) && !empty($proposals[$string['id']]) ) {
											foreach($proposals[$string['id']] as $proposal) {
												
												// Decrease counter from earlier if there's a translated proposal for this string (in user's language)
												if( $proposal['language'] === $_SESSION['language'] && $proposal['id'] == $string[ $proposal['language'].'_id' ]) {
													unset($num_new[ $string['folder'] ][ $string['id'] ]);
												}
												
												$string['languages'][] = $proposal['language'];
												
												echo render_component($translation_template, [
													'language_name'       => $translate->allowed_languages[ $proposal['language'] ],
													'language'            => $proposal['language'],
													'content'             => $proposal['content'],
													'is_accepted'         => $proposal['id'] == $string[ $proposal['language'].'_id' ] ? null : 'any--hidden',
													'user_icon'           => $proposal['user']['icon'],
													'user_is_vip'         => $proposal['user']['is_vip'],
													'user_username'       => $proposal['user']['username'],
													'date_occurred'       => substr($proposal['date_occurred'], 0, 10),
													'id'                  => $proposal['id'],
													'upvote_is_checked'   => (is_array($user_upvotes) && in_array($proposal['id'], $user_upvotes) ? 'checked' : null),
													'num_votes'           => $proposal['num_votes'] ?: 0,
													'downvote_is_checked' => (is_array($user_downvotes) && in_array($proposal['id'], $user_downvotes) ? 'checked' : null),
												]);
												
											}
										}
									?>
									
									<!-- Context -->
									<li class="data__container any--weaken-color">
										<span class="languages any--hidden"><?= implode(',', $string['languages']); ?></span>
										<?php
											if($string['folder']) {
												?>
													<div class="data__item">
														<h5>
															Section
														</h5>
														<span class="accepted__folder" data-folder="<?= $string['folder']; ?>"><?= $string['folder'] === 'php' ? 'UI' : $string['folder']; ?></span>
													</div>
												<?php
											}
										?>
										<?php
											if($string['context']) {
												?>
													<div class="data__item">
														<h5>
															Context
														</h5>
														<span class="accepted__context"><?= $string['context']; ?></span>
													</div>
												<?php
											}
										?>
										<!--<div class="data__item">
											<h5>
												ID
											</h5>
											<?= $string['id']; ?>
										</div>-->
									</li>
									
									<!-- Add translation -->
									<li class="input__row details__add">
										
										<div class="input__group">
											<label class="input__label">Language</label>
											<select class="input" name="language[]" placeholder="language">
												<option></option>
												<?php
													foreach($translate->allowed_languages as $language_key => $language) {
														if($language_key != 'en') {
															echo '<option value="'.$language_key.'"'.($language_key == $translate->language ? ' selected ' : null).'>'.$language.'</option>';
														}
													}
												?>
											</select>
											<input name="en_id[]" value="<?= $string['id']; ?>" hidden />
										</div>
										
										<div class="input__group any--flex-grow">
											<label class="input__label">Your translation</label>
											<input class="any--flex-grow" name="content[]" placeholder="translation..." />
										</div>
										
										<div class="input__group">
											<button class="symbol__plus" name="add[]" type="submit">Add</button>
											<span data-role="status"></span>
										</div>
										
									</li>
									
								</ul>
								
								</form>
							</li>
						<?php
					}
				}
			?>
			
		</ul>
		
	</div>
	
	<!-- Filters -->
	<div class="filter__wrapper">
		
		<ul class="text text--outlined">
			
			<li class="input__row" style="z-index:2;">
				<div class="input__group any--flex-grow">
					<label class="input__label">Language</label>
					<select class="input any--flex-grow" name="filter_language" placeholder="language">
						<option>all</option>
						<?php
							foreach($translate->allowed_languages as $language_key => $language) {
								if($language_key != 'en') {
									echo '<option value="'.$language_key.'" '.($translate->language === $language_key ? 'selected' : null).'>'.$language.'</option>';
								}
							}
						?>
					</select>
				</div>
			</li>
			
			<li class="input__row" style="z-index:3;">
				<div class="input__group any--flex-grow">
					<label class="input__label">Section</label>
					<ul class="any--flex-grow <?= count($sections) > 6 ? 'filter--scroll' : null; ?>">
						<?php
							foreach($sections as $section) {
								echo '<li>';
								echo '<a class="filter--section" href="#'.$section.'">'.($section === 'php' ? 'UI' : $section).'</a>';
								echo is_array($num_new[$section]) && count($num_new[ $section ]) ? ' <span class="any__note">'.count($num_new[$section]).' new</span>' : null;
								echo '</li>';
							}
						?>
					</ul>
				</div>
			</li>
			
			<!--<li class="input__row any--hidden">
				<div class="input__group">
					<label class="input__label">Status</label>
					<label class="input__radio">
						<input class="input__choice" name="filter_status" type="radio" checked />
						<span class="symbol__unchecked">all</span>
					</label>
					<label class="input__radio">
						<input class="input__choice" name="filter_status" type="radio" />
						<span class="symbol__unchecked">translated</span>
					</label>
					<label class="input__radio">
						<input class="input__choice" name="filter_status" type="radio" />
						<span class="symbol__unchecked">untranslated</span>
					</label>
				</div>
			</li>-->
			
		</ul>
		
	</div>
	
	</div>
	
</div>

<?php if($_SESSION['is_boss']) { ?>
<div class="col c1">
	<div>
		
		<h2>
			<?= lang('Add string', 'テキストを追加', 'div'); ?>
		</h2>
		<form action="" class="text string__container" enctype="multipart/form-data" method="post" name="add_string">
			
			<div class="input__row">
				
				<div class="input__group">
					<label class="input__label">ID</label>
					<input name="id" placeholder="id" size="2" value="" />
				</div>
				
				<div class="input__group">
					<label class="input__label">Section</label>
					<select class="input" name="folder" placeholder="section">
						<option></option>
						<?php
							foreach($sections as $section) {
								echo '<option value="'.$section.'">'.$section.'</option>';
							}
						?>
					</select>
				</div>
				
				<div class="input__group any--flex-grow">
					<label class="input__label">String</label>
					<input class="any--flex-grow" name="content" placeholder="string" />
				</div>
				
			</div>
			
			<div class="input__row">
				
				<div class="input__group any--flex-grow">
					<label class="input__label">Context</label>
					<input class="any--flex-grow" name="context" placeholder="more context" />
				</div>
				
				<div class="input__group">
					<button class="symbol__plus" data-role="submit" name="submit_string" type="submit">Add</button>
					<span data-role="status"></span>
				</div>
				
			</div>
		</form>
		
	</div>
</div>
<?php } ?>

<?php
	$documentation_page = [ 'translation' ];
	include('../documentation/index.php');
?>