<?php

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

$sql_translations = '
	SELECT
		translations.*,
		ja.translation AS ja_translation,
		fr.translation AS fr_translation
	FROM translations
	LEFT JOIN translations_proposals AS ja ON ja.id=translations.ja
	LEFT JOIN translations_proposals AS fr ON fr.id=translations.fr
';
$stmt_translations = $pdo->prepare($sql_translations);
$stmt_translations->execute();
$rslt_translations = $stmt_translations->fetchAll();

// Make translations associative
if(is_array($rslt_translations) && !empty($rslt_translations)) {
	foreach($rslt_translations as $translation) {
		$translations[$translation['id']] = $translation;
		unset($rslt_translations);
	}
}

$sql_proposals = 'SELECT * FROM translations_proposals';
$stmt_proposals = $pdo->prepare($sql_proposals);
$stmt_proposals->execute();
$proposals = $stmt_proposals->fetchAll();

// Get translation users
if(is_array($proposals) && !empty($proposals)) {
	foreach($proposals as $proposal_key => $proposal) {
		$proposals[$proposal_key]['user'] = $access_user->access_user([ 'id' => $proposal['user_id'], 'get' => 'name' ]);
	}
}

// Get current user's votes
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

print_r($proposals);

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
	.accepted__header, .proposed__header {
		background: hsl(var(--background));
		position: sticky;
		margin: -1rem !important;
		margin-bottom: 0 !important;
		padding: 1rem !important;
		top: 3rem;
		z-index: 1;
	}
	.accepted__row, .proposed__row {
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
	.accepted__details {
		background: hsl(var(--background--secondary));
		background-image: linear-gradient(to bottom, hsla(var(--background),100), hsla(var(--background),0) 1rem);
		margin: 1rem -1rem -1rem -1rem;
		padding-left: 0.5rem;
		padding-top: 2rem;
		width: calc(100% + 2rem);
	}
	.accepted__container {
		margin: 0;
	}
	.accepted__container + .accepted__container {
		margin-top: 1rem;
	}
	
	.proposed__en {
		display: inline-block;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		width: 100px;
	}
	.proposed__translation {
		margin-right: auto;
	}
	.proposed__user {
		display: inline-block;
		margin-right: 0.5rem;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		width: 6rem;
	}
	.proposed__date {
		display: inline-block;
		margin-right: 0.5rem;
		width: 5rem;
	}
	.proposed__vote {
		display: inline-block;
		width: 4rem;
	}
</style>

<div class="col c1">
	<div>
		
		<h2>
			Accepted translations
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
				if(is_array($translations) && !empty($translations)) {
					foreach($translations as $translation_row) {
						?>
							<li class="accepted__row" x-data="{open:false}">
								
								<!-- Text -->
								<span class="accepted__en"><?= $translation_row['en']; ?></span>
								<?php
									foreach($allowed_languages as $language_key => $language) {
										if($language_key != 'en') {
											echo '<span class="accepted__lang '.($translation_row[$language_key] ? 'symbol__checkbox--checked' : 'symbol__checkbox--unchecked').'"></span>';
										}
									}
								?>
								<span class="accepted__more"><a class="symbol__down-caret" x-on:click="open=!open">more</a></span>
								
								<div class="accepted__details text" x-show="open">
									
									<!-- Add translation -->
									<div class="accepted__container input__row">
										
										<div class="input__group">
											<label class="input__label">Language</label>
											<select class="input" name="translation_language" placeholder="language">
												<option></option>
												<?php
													foreach($allowed_languages as $language_key => $language) {
														if($language_key != 'en') {
															echo '<option value="'.$language_key.'">'.$language.'</option>';
														}
													}
												?>
											</select>
										</div>
										
										<div class="input__group any--flex-grow">
											<label class="input__label">Your translation</label>
											<input class="any--flex-grow" name="translation" placeholder="translation..." />
										</div>
										
										<div class="input__group">
											<button name="add_translation" type="button">
												Add
											</button>
										</div>
										
									</div>
									
									<!-- Details -->
									<div class="accepted__container data__container any--weaken-color">
										
										<?php
											foreach($allowed_languages as $language_key => $language) {
												if($language_key != 'en') {
													if(is_numeric($translation_row[$language_key])) {
														?>
															<div class="data__item">
																<h5 style="line-height:1rem;"><?= $language; ?></h5>
																<?= $translation_row[ $language_key.'_translation' ]; ?>
															</div>
														<?php
													}
												}
											}
										?>
										
										<div class="data__item">
											<h5>ID</h5>
											<?= $translation_row['id']; ?>
										</div>
										
										<?php
											if($translation_row['context']) {
												?>
													<div class="data__item">
														<h5>Context</h5>
														<?= $translation_row['context']; ?>
													</div>
												<?php
											}
										?>
										
										<div class="data__item">
											<h5>vote</h5>
											<a href="">review and vote</a>
										</div>
										
									</div>
									
								</div>
							</li>
						<?php
					}
				}
			?>
			
		</ul>
		
		<h2>
			Translation proposals
		</h2>
		
		<ul class="text">
			
			<!-- Header -->
			<li class="proposed__row proposed__header">
				<label class="h5 proposed__en">Original</label>
				<label class="h5 proposed__translation">Translation</label>
				<label class="h5 proposed__user">User</label>
				<label class="h5 proposed__date">Date</label>
				<label class="h5 proposed__vote">Vote</label>
			</li>
			
			<?php
				foreach($proposals as $proposal) {
					?>
						<li class="proposed__row">
							<span class="proposed__en any--weaken"><?= $translations[$proposal['translation_id']]['en']; ?></span>
							<span class="proposed__translation"><span class="any__note"><?= strtoupper($proposal['language']); ?></span> <?= $proposal['translation']; ?></span>
							<span class="proposed__user any--weaken"><a class="a--inherit user" data-icon="<?= $proposal['user']['icon']; ?>" data-is-vip="<?= $proposal['user']['is_vip']; ?>" href="<?= $proposal['user']['url']; ?>"><?= $proposal['user']['username']; ?></a></span>
							<span class="proposed__date any--weaken"><?= substr($proposal['date_added'], 0, 10); ?></span>
							<span class="proposed__vote"><?php
								echo '
									<span class="tag__voting any--weaken-color">
										
										<label class="tag__vote tag__upvote" data-vote="upvote" data-id="'.$proposal['id'].'">
											<input class="input__choice" type="checkbox" '.(is_array($user_upvotes) && in_array($proposal['id'], $user_upvotes) ? 'checked' : null).' />
											<span class="tag__status symbol__up-caret symbol--standalone"></span>
										</label>
										
										<span class="tag__num any--weaken-size" data-tag-id="'.$proposal['id'].'" data-num-tags="'.$proposal['num_upvotes'].'"></span>
										
										<label class="tag__vote tag__status tag__downvote" data-vote="downvote" data-id="'.$proposal['id'].'">
											<input class="input__choice" type="checkbox" '.(is_array($user_downvotes) && in_array($proposal['id'], $user_downvotes) ? 'checked' : null).' />
											<span class="symbol__down-caret symbol--standalone"></span>
										</label>
										
									</span>
								';
							?></span>
						</li>
					<?php
				}
			?>
			
		</ul>
		
		
		<table class="text">
			
			<thead>
				<?php
					foreach($proposals[0] as $key => $proposal) {
						?>
							<td class="h5"><?= $key; ?></td>
						<?php
					}
				?>
			</thead>
			
		</table>
		
	</div>
</div>


<style>
	.tag--subgenre {
		align-items: stretch;
		margin-bottom: 1rem;
	}
	.tag--subgenre .text {
		display: flex;
		flex: 1;
		margin-bottom: 0;
	}
	
	.tag__voting {
		background: hsl(var(--background--secondary));
		border: 1px solid hsl(var(--background--secondary));
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