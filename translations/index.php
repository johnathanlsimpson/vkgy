<?php

function init_tr() {
	
	$language = sanitize($_COOKIE['language']) ?: sanitize($_SESSION['language']);
	$allowed_languages = [ 'ja' => '日本語', 'es' => 'español' ];
	global $translations;
	
	if(strlen($language) && in_array($language, array_keys($allowed_languages))) {
		
		$folder = dirname(__FILE__);
		$trans_file = $folder.'/lang.'.$language;
		
		if(strlen($folder) && file_exists($trans_file)) {
			
			$file_contents = file_get_contents($trans_file);
			$file_contents = $file_contents ? gzuncompress($file_contents) : null;
			$file_contents = $file_contents ? unserialize($file_contents) : null;
			
			if(is_array($file_contents) && !empty($file_contents)) {
				$translations = is_array($translations) ? $translations : [];
				$translations = array_merge($translations, $file_contents);
			}
			
		}
		
	}
	
}

function tr($string) {
	
	$language = sanitize($_COOKIE['language']) ?: sanitize($_SESSION['language']);
	$allowed_languages = [ 'ja' => '日本語', 'es' => 'español' ];
	global $translations;
	
	if(strlen($language) && in_array($language, array_keys($allowed_languages))) {
		
		if(is_array($translations) && !empty($translations)) {
			
			if(strlen($translations[$string])) {
				return $translations[$string];
			}
			else {
				return $string;
			}
			
		}
		
	}
	
}

$_SESSION['language'] = 'es';

init_tr();

echo '*'.tr('Profile');

include_once('../php/function-render_component.php');

$access_user = new access_user($pdo);

script([
	'/scripts/external/script-selectize.js',
	'/scripts/script-initSelectize.js',
	'/translations/script-index.js',
]);
	
style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
]);

// Get translation strings
$sql_translations = 'SELECT translations.* FROM translations';
$stmt_translations = $pdo->prepare($sql_translations);
$stmt_translations->execute();
$strings = $stmt_translations->fetchAll();

// Get proposed translations
$sql_proposals = '
SELECT translations_proposals.*, SUM(translations_votes.vote) AS num_votes 
FROM translations_proposals 
LEFT JOIN translations_votes ON translations_votes.proposal_id=translations_proposals.id
GROUP BY translations_proposals.id
ORDER BY en_id ASC, language ASC, date_occurred DESC';
$stmt_proposals = $pdo->prepare($sql_proposals);
$stmt_proposals->execute();
$rslt_proposals = $stmt_proposals->fetchAll();

// Get proposals' users
if(is_array($rslt_proposals) && !empty($rslt_proposals)) {
	foreach($rslt_proposals as $proposal_key => $proposal) {
		$proposal['user'] = $access_user->access_user([ 'id' => $proposal['user_id'], 'get' => 'name' ]);
		$proposals[$proposal['en_id']][] = $proposal;
	}
}

// Get votes of user who's viewing page
if($_SESSION['is_signed_in']) {
	$sql_votes = 'SELECT * FROM translations_votes WHERE user_id=?';
	$stmt_votes = $pdo->prepare($sql_votes);
	$stmt_votes->execute([ $_SESSION['user_id'] ]);
	$rslt_votes = $stmt_votes->fetchAll();
	
	// Transform votes into upvote/downvote arrays
	if(is_array($rslt_votes) && !empty($rslt_votes)) {
		foreach($rslt_votes as $vote) {
			if($vote['vote'] > 0) {
				$user_upvotes[] = $vote['proposal_id'];
			}
			else {
				$user_downvotes[] = $vote['proposal_id'];
			}
		}
	}
}

$allowed_languages = [
	'en' => 'English',
	'ja' => '日本語',
	'es' => 'español',
	'fr' => 'français',
	'ru' => 'Русский',
	'zh' => '中文',
];

?>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

<style>
	.accepted__header {
		background: hsl(var(--background));
		position: sticky;
		margin: -1rem !important;
		margin-bottom: 0 !important;
		padding: 1rem !important;
		top: 3rem;
		z-index: 1;
	}
	.accepted__row {
		display: flex;
		flex-wrap: wrap;
	}
	.accepted__en {
		margin-right: auto;
	}
	.accepted__lang {
		display: inline-block;
		width: 2rem;
	}
	.accepted__more {
		display: inline-block;
		text-align: right;
		width: 4rem;
	}
	
	.details__container {
		background: hsl(var(--background--secondary));
		background-image: linear-gradient(to bottom, hsla(var(--background),100), hsla(var(--background),0) 1rem);
		margin: 1rem -1rem -1rem -1rem;
		padding-top: 2rem;
		width: calc(100% + 2rem);
	}
	.details__proposal {
		align-items: flex-start;
	}
	.details__user, .details__date {
		line-height: 1.5rem;
		margin-right: 0.5rem;
	}
	
	li.data__container {
		clip-path: polygon(0.5rem 0, 100% 0, 100% 100%, 0.5rem 100%);
		margin-bottom: 0.5rem;
		margin-top: 0;
		padding-bottom: 0.5rem;
		padding-top: 0.5rem;
	}
	li.input__row {
		clip-path: polygon(0.5rem 0, 100% 0, 100% 600%, 0.5rem 600%);
		padding-top: 0.5rem;
		z-index: 1;
	}
</style>

<div class="col c1">
	<div>
		
		<h2>
			<?= lang('Translations', '翻訳', 'div'); ?>
		</h2>
		<ul class="text">
			
			<!-- Header -->
			<li class="accepted__row accepted__header">
				<label class="h5 accepted__en">English phrase</label>
				<?php
					foreach($allowed_languages as $language_key => $language) {
						if($language_key != 'en') {
							echo '<label class="h5 accepted__lang">'.$language_key.'</label>';
						}
					}
				?>
				<label class="h5 accepted__more">&nbsp;</label>
			</li>
			
			<!-- Translations -->
			<?php
				if(is_array($strings) && !empty($strings)) {
					foreach($strings as $string) {
						?>
							<li class="" x-data="{open:true}">
								<form action="/translations/function-update_translation.php" class="accepted__row" enctype="multipart/form-data" method="post" name="add_translation[]">
								
								<!-- Text -->
								<span class="accepted__en"><?= $string['content']; ?></span>
								<?php
									foreach($allowed_languages as $language_key => $language) {
										if($language_key != 'en') {
											echo '<span class="accepted__lang '.(is_numeric($string[$language_key.'_id']) ? 'symbol__checkbox--checked' : 'symbol__checkbox--unchecked').'"></span>';
										}
									}
								?>
								<span class="accepted__more"><a class="symbol__down-caret" x-on:click="open=!open">more</a></span>
								
								<ul class="details__container text" x-show="open">
									
									<!-- View current translations -->
									<?php
										if(!$translation_template) {
											?>
												<template id="template-translation">
													<?php
														ob_start();
														?>
															<li class="any--flex details__proposal">
																<div class="any--flex-grow any--weaken-color">
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
										
										if( is_array($proposals[$string['id']]) && !empty($proposals[$string['id']]) ) {
											foreach($proposals[$string['id']] as $proposal) {
												
												echo render_component($translation_template, [
													'language_name'       => $allowed_languages[ $proposal['language'] ],
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
										<?php
											if($string['page']) {
												?>
													<div class="data__item">
														<h5>
															Page
														</h5>
														<?= $string['page']; ?>
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
														<?= $string['context']; ?>
													</div>
												<?php
											}
										?>
										<div class="data__item">
											<h5>
												ID
											</h5>
											<?= $string['id']; ?>
										</div>
									</li>
									
									<!-- Add translation -->
									<li class="input__row details__add">
										
										<div class="input__group">
											<label class="input__label">Language</label>
											<select class="input" name="language[]" placeholder="language">
												<option></option>
												<?php
													foreach($allowed_languages as $language_key => $language) {
														if($language_key != 'en') {
															echo '<option value="'.$language_key.'">'.$language.'</option>';
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
</div>


<style>
	.tag__voting {
		background: hsl(var(--background--secondary));
		border: 1px solid hsl(var(--background));
		border-radius: 3px;
		display: inline-flex;
		line-height: 1.5rem;
		text-align: center;
	}
	.tag--subgenre .tag__voting {
		flex-direction: column;
	}
	.tag__num {
		padding: 0 5px;
	}
	.tag__num::before {
		content: attr(data-num-tags);
	}
	.tag__num[data-num-tags^="-"] {
		color: red;
	}
	.tag__upvote, .tag__downvote {
		color: hsl(var(--background--bold));
		cursor: pointer;
		padding: 0 5px;
		position: initial;
	}
	.tag__upvote:hover, .tag__downvote:hover {
		color: hsl(var(--text));
	}
	.tag__upvote:hover::after, .tag__downvote:hover::after {
		background: linear-gradient(var(--vote-dir), var(--vote-bg), transparent);
		bottom: 0;
		content: "";
		display: block;
		left: 0;
		pointer-events: none;
		position: absolute;
		right: 0;
		top: 0;
	}
	.tag__upvote:hover {
		--vote-bg: rgba(0,255,0,0.1);
		--vote-dir: to right;
	}
	.tag__downvote:hover {
		--vote-bg: rgba(255,0,0,0.1);
		--vote-dir: to left;
	}
	.tag--subgenre .tag__upvote:hover {
		--vote-dir: to bottom;
	}
	.tag--subgenre .tag__downvote:hover {
		--vote-dir: to top;
	}
</style>